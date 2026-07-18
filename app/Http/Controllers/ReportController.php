<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    private function companyScope(Request $request): ?Company
    {
        $companyId = $request->input('company_id') ?? session('filter_company_id');
        if (! $companyId) {
            return null;
        }
        $company = Company::find((int) $companyId);

        return ($company && $request->user()->canViewCompany($company)) ? $company : null;
    }

    private function allowedCompanyIds(Request $request, ?Company $scope): array
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return $scope ? [$scope->id] : Company::pluck('id')->toArray();
        }

        return $user->getAllowedCompanyIds();
    }

    public function index(Request $request): View
    {
        $scope = $this->companyScope($request);
        $companyIds = $this->allowedCompanyIds($request, $scope);

        $totalEmployees = User::whereIn('company_id', $companyIds)->where('is_active', true)->count();
        $totalCompanies = $scope ? 1 : Company::whereIn('id', $companyIds)->count();
        $activeContracts = Contract::whereIn('company_id', $companyIds)->where('status', 'active')->count();

        $totalPayroll = (float) Salary::whereIn('company_id', $companyIds)
            ->where('status', 'active')
            ->sum('net_salary');

        $byDepartment = Department::whereIn('company_id', $companyIds)
            ->withCount(['users' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('users_count')
            ->limit(10)
            ->get();

        $leaveStats = [
            'pending' => Leave::whereIn('company_id', $companyIds)->where('status', 'pending')->count(),
            'approved' => Leave::whereIn('company_id', $companyIds)->where('status', 'approved')->count(),
            'rejected' => Leave::whereIn('company_id', $companyIds)->where('status', 'rejected')->count(),
        ];

        $companies = $request->user()->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        return view('reports.index', [
            'filterCompany' => $scope?->id,
            'companies' => $companies,
            'totalEmployees' => $totalEmployees,
            'totalCompanies' => $totalCompanies,
            'activeContracts' => $activeContracts,
            'totalPayroll' => $totalPayroll,
            'byDepartment' => $byDepartment,
            'leaveStats' => $leaveStats,
        ]);
    }

    public function export(Request $request): Response
    {
        $scope = $this->companyScope($request);
        $companyIds = $this->allowedCompanyIds($request, $scope);

        $users = User::whereIn('company_id', $companyIds)
            ->where('is_active', true)
            ->with(['company', 'department', 'designation', 'activeSalary'])
            ->orderBy('name')
            ->get();

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Name', 'Email', 'Company', 'Department', 'Designation', 'Employee ID', 'Net Salary', 'Currency']);

        foreach ($users as $u) {
            fputcsv($csv, [
                $u->name,
                $u->email,
                $u->company?->name,
                $u->department?->name,
                $u->designation?->name,
                $u->employee_id,
                $u->activeSalary?->net_salary,
                $u->activeSalary?->currency,
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        $filename = 'hrms-report-'.Carbon::now()->format('Y-m-d').'.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
