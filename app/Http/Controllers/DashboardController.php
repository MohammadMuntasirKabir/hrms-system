<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Leave;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $stats = $this->getStats($user, $request);
        $recentItems = $this->getRecentItems($user, $request);

        if ($user->isSuperAdmin()) {
            $actionData = $this->getActionRequiredData($user);

            return view('dashboard.super-admin', [
                'user' => $user,
                'stats' => $stats,
                'recentItems' => $recentItems,
                'pendingLeaves' => $actionData['pendingLeaves'],
                'expiringContracts' => $actionData['expiringContracts'],
            ]);
        }

        if ($user->hasAnyRole(['company_admin', 'hr_manager'])) {
            $actionData = $this->getActionRequiredData($user);

            return view('dashboard.admin', [
                'user' => $user,
                'stats' => $stats,
                'recentItems' => $recentItems,
                'pendingLeaves' => $actionData['pendingLeaves'],
                'expiringContracts' => $actionData['expiringContracts'],
            ]);
        }

        if ($user->hasRole('department_head')) {
            $teamData = $this->getDepartmentHeadData($user);

            return view('dashboard.dept-head', [
                'user' => $user,
                'stats' => $stats,
                'team' => $teamData['team'],
                'pendingLeaves' => $teamData['pendingLeaves'],
                'expiringContracts' => $teamData['expiringContracts'],
            ]);
        }

        $employeeData = $this->getEmployeeData($user);

        return view('dashboard.employee', [
            'user' => $user,
            'stats' => $stats,
            'activeContract' => $employeeData['activeContract'],
            'myLeaves' => $employeeData['myLeaves'],
            'leaveSummary' => $employeeData['leaveSummary'],
        ]);
    }

    private function getDepartmentHeadData(User $user): array
    {
        $companyIds = $user->getAllowedCompanyIds();
        $departmentId = $user->department_id;

        $team = User::query()
            ->where('department_id', $departmentId)
            ->whereIn('company_id', $companyIds)
            ->where('is_active', true)
            ->with(['designation', 'department'])
            ->orderBy('name')
            ->get();

        $teamIds = $team->pluck('id')->all();

        $pendingLeaves = Leave::query()
            ->whereIn('user_id', $teamIds)
            ->where('status', 'pending')
            ->with(['user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $expiringContracts = Contract::query()
            ->whereIn('user_id', $teamIds)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->with(['user'])
            ->orderBy('end_date')
            ->limit(10)
            ->get();

        return [
            'team' => $team,
            'pendingLeaves' => $pendingLeaves,
            'expiringContracts' => $expiringContracts,
        ];
    }

    private function getEmployeeData(User $user): array
    {
        $activeContract = $user->activeContract;

        $myLeaves = Leave::query()
            ->where('user_id', $user->id)
            ->with(['approver'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $leaveSummary = [
            'total' => Leave::where('user_id', $user->id)->count(),
            'pending' => Leave::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => Leave::where('user_id', $user->id)->where('status', 'approved')->count(),
        ];

        return [
            'activeContract' => $activeContract,
            'myLeaves' => $myLeaves,
            'leaveSummary' => $leaveSummary,
        ];
    }

    private function getCompanyFilter(Request $request): ?int
    {
        $companyId = $request->input('company_id') ?? session('filter_company_id');

        return $companyId ? (int) $companyId : null;
    }

    private function getStats(User $user, Request $request): array
    {
        $companyId = $this->getCompanyFilter($request);
        $cacheKey = 'dashboard_stats_'.$user->id.'_'.($companyId ?? 'all');

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($user, $companyId) {
            $companyIds = $user->isSuperAdmin()
                ? ($companyId ? [$companyId] : null)
                : $user->getAllowedCompanyIds();

            $userQuery = User::query();
            $deptQuery = Department::query();
            $desigQuery = Designation::query();
            $contractQuery = Contract::query();
            $salaryQuery = Salary::query();

            if ($companyIds !== null) {
                $userQuery->whereIn('company_id', $companyIds);
                $deptQuery->whereIn('company_id', $companyIds);
                $desigQuery->whereIn('company_id', $companyIds);
                $contractQuery->whereIn('company_id', $companyIds);
                $salaryQuery->whereIn('company_id', $companyIds);
            }

            $activeContractQuery = (clone $contractQuery)->where('status', 'active');
            $expiringQuery = (clone $activeContractQuery)
                ->whereNotNull('end_date')
                ->where('end_date', '<=', now()->addDays(30))
                ->where('end_date', '>=', now());

            return [
                'totalEmployees' => (clone $userQuery)->count(),
                'activeEmployees' => (clone $userQuery)->where('is_active', true)->count(),
                'totalDepartments' => (clone $deptQuery)->count(),
                'totalDesignations' => (clone $desigQuery)->count(),
                'activeContracts' => (clone $activeContractQuery)->count(),
                'expiringContracts' => (clone $expiringQuery)->count(),
                'totalPayroll' => (clone $salaryQuery)->where('status', 'active')->sum('net_salary'),
                'avgSalary' => (clone $salaryQuery)->where('status', 'active')->avg('net_salary'),
                'totalCompanies' => $user->isSuperAdmin()
                    ? ($companyId ? 1 : Company::count())
                    : Company::whereIn('id', $companyIds ?? [])->count(),
            ];
        });
    }

    private function getRecentItems(User $user, Request $request): array
    {
        $companyId = $this->getCompanyFilter($request);
        $companyIds = $user->isSuperAdmin()
            ? ($companyId ? [$companyId] : null)
            : $user->getAllowedCompanyIds();

        $userQuery = User::query()->with(['designation', 'department', 'roles']);
        $companyQuery = Company::query()->withCount('users');
        $contractQuery = Contract::query()->with(['user.designation', 'department']);

        if ($companyIds !== null) {
            $userQuery->whereIn('company_id', $companyIds);
            $companyQuery->whereIn('id', $companyIds);
            $contractQuery->whereIn('company_id', $companyIds);
        }

        return [
            'recentEmployees' => (clone $userQuery)->latest()->take(5)->get(),
            'recentCompanies' => $user->isSuperAdmin()
                ? (clone $companyQuery)->latest()->take(5)->get()
                : collect(),
            'recentContracts' => (clone $contractQuery)->latest('start_date')->take(5)->get(),
        ];
    }

    private function getActionRequiredData(User $user): array
    {
        $companyIds = $user->isSuperAdmin()
            ? null
            : $user->getAllowedCompanyIds();

        $pendingLeaveQuery = Leave::query()
            ->where('status', 'pending')
            ->with(['user.department', 'company'])
            ->orderByDesc('created_at');

        $expiringQuery = Contract::query()
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->with(['user.department', 'company'])
            ->orderBy('end_date');

        if ($companyIds !== null) {
            $pendingLeaveQuery->whereIn('company_id', $companyIds);
            $expiringQuery->whereIn('company_id', $companyIds);
        }

        return [
            'pendingLeaves' => $pendingLeaveQuery->limit(10)->get(),
            'expiringContracts' => $expiringQuery->limit(10)->get(),
        ];
    }
}
