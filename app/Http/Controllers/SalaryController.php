<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryController extends Controller
{
    private function authorizeCompany(Request $request, Company $company): void
    {
        if (! $request->user()->canViewCompany($company)) {
            abort(403, 'You do not have access to this company\'s data.');
        }
    }

    private function authorizeSalaryAccess(Request $request): void
    {
        if (! $request->user()->canViewSalaries()) {
            abort(403, 'You do not have permission to view salary information.');
        }
    }

    private function getCompanyFilter(Request $request): ?int
    {
        $companyId = $request->input('company_id') ?? session('filter_company_id');

        return $companyId ? (int) $companyId : null;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->authorizeSalaryAccess($request);
        $companyId = $this->getCompanyFilter($request);

        $query = Salary::with(['user.department', 'user.designation', 'company', 'department', 'designation']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        } elseif (! $user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive', 'revised'])) {
            $query->where('status', $request->status);
        }

        $salaries = $query->orderByDesc('effective_from')->paginate(20);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $departments = Department::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $totalPayroll = (clone $query)->sum('net_salary');
        $avgSalary = (clone $query)->avg('net_salary');

        return view('salaries.index', [
            'salaries' => $salaries,
            'currentUser' => $user,
            'companies' => $companies,
            'departments' => $departments,
            'filterCompany' => $companyId,
            'filterDepartment' => $request->department_id,
            'filterStatus' => $request->status,
            'totalPayroll' => $totalPayroll,
            'avgSalary' => $avgSalary,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $this->authorizeSalaryAccess($request);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $selectedCompanyId = $request->company_id ?? ($companies->count() === 1 ? $companies->first()->id : null);

        $users = User::where('is_active', true)
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $departments = Department::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $designations = Designation::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('title')
            ->get();

        return view('salaries.create', [
            'companies' => $companies,
            'users' => $users,
            'departments' => $departments,
            'designations' => $designations,
            'selectedCompanyId' => $selectedCompanyId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeSalaryAccess($request);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'pay_frequency' => ['required', 'string', 'in:monthly,bi_weekly,weekly'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'string', 'in:active,inactive,revised'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $this->authorizeCompany($request, $company);

        Salary::where('user_id', $validated['user_id'])
            ->where('status', 'active')
            ->update(['status' => 'inactive', 'effective_until' => now()]);

        $validated['allowances'] = $validated['allowances'] ?? 0;
        $validated['deductions'] = $validated['deductions'] ?? 0;
        $validated['net_salary'] = $validated['base_salary'] + $validated['allowances'] - $validated['deductions'];
        $validated['currency'] = $validated['currency'] ?? 'BDT';
        $validated['created_by'] = $user->id;

        $salary = Salary::create($validated);

        return redirect()->route('salaries.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Salary for '.$salary->user->name.' created successfully.');
    }

    public function show(Request $request, Salary $salary): View
    {
        $user = $request->user();
        $this->authorizeSalaryAccess($request);
        $this->authorizeCompany($request, $salary->company);

        $salary->load(['user.department', 'user.designation', 'company', 'department', 'designation', 'contract', 'creator']);

        return view('salaries.show', [
            'salary' => $salary,
            'currentUser' => $user,
        ]);
    }

    public function edit(Request $request, Salary $salary): View
    {
        $user = $request->user();
        $this->authorizeSalaryAccess($request);
        $this->authorizeCompany($request, $salary->company);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $users = User::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $departments = Department::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $designations = Designation::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('title')
            ->get();

        return view('salaries.edit', [
            'salary' => $salary,
            'companies' => $companies,
            'users' => $users,
            'departments' => $departments,
            'designations' => $designations,
        ]);
    }

    public function update(Request $request, Salary $salary): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeSalaryAccess($request);
        $this->authorizeCompany($request, $salary->company);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'pay_frequency' => ['required', 'string', 'in:monthly,bi_weekly,weekly'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'string', 'in:active,inactive,revised'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['allowances'] = $validated['allowances'] ?? 0;
        $validated['deductions'] = $validated['deductions'] ?? 0;
        $validated['net_salary'] = $validated['base_salary'] + $validated['allowances'] - $validated['deductions'];
        $validated['currency'] = $validated['currency'] ?? 'BDT';

        $salary->update($validated);

        return redirect()->route('salaries.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Salary for '.$salary->user->name.' updated successfully.');
    }

    public function destroy(Request $request, Salary $salary): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeSalaryAccess($request);
        $this->authorizeCompany($request, $salary->company);

        $userName = $salary->user->name;
        $salary->delete();

        return redirect()->route('salaries.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Salary for '.$userName.' deleted.');
    }
}
