<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
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
            return view('dashboard.super-admin', [
                'user' => $user,
                'stats' => $stats,
                'recentItems' => $recentItems,
            ]);
        }

        if ($user->hasAnyRole(['company_admin', 'hr_manager'])) {
            return view('dashboard.admin', [
                'user' => $user,
                'stats' => $stats,
                'recentItems' => $recentItems,
            ]);
        }

        if ($user->hasRole('department_head')) {
            return view('dashboard.dept-head', [
                'user' => $user,
                'stats' => $stats,
            ]);
        }

        return view('dashboard.employee', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    private function getCompanyFilter(Request $request): ?int
    {
        $companyId = $request->input('company_id') ?? session('filter_company_id');
        return $companyId ? (int) $companyId : null;
    }

    private function getStats(User $user, Request $request): array
    {
        $companyId = $this->getCompanyFilter($request);
        $cacheKey = 'dashboard_stats_' . $user->id . '_' . ($companyId ?? 'all');

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
}
