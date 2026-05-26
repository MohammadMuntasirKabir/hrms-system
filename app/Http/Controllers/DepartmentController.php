<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    private function authorizeCompany(Request $request, Company $company): void
    {
        if (! $request->user()->canViewCompany($company)) {
            abort(403);
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
        $companyId = $this->getCompanyFilter($request);

        $query = Department::with(['company', 'headUser'])
            ->withCount(['users', 'designations', 'contracts', 'childDepartments']);

        if (! $user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        $departments = $query->orderBy('name')->paginate(20);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        return view('departments.index', [
            'departments' => $departments,
            'currentUser' => $user,
            'companies' => $companies,
            'filterCompany' => $companyId,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $companyId = $this->getCompanyFilter($request);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $parentDepartments = Department::where('is_active', true)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $heads = User::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('departments.create', compact('companies', 'parentDepartments', 'heads'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_department_id' => ['nullable', 'exists:departments,id'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'company_id' => ['required', 'exists:companies,id'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $this->authorizeCompany($request, $company);

        if (! $user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        $validated['is_active'] = true;
        $department = Department::create($validated);

        return redirect()->route('departments.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Department "'.$department->name.'" created successfully.');
    }

    public function show(Request $request, Department $department): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $department->company);

        $department->load(['company', 'headUser', 'parentDepartment', 'childDepartments'])
            ->loadCount(['users', 'designations', 'contracts']);

        $employees = $department->users()->with(['designation', 'activeContract', 'activeSalary'])->latest()->take(10)->get();
        $designations = $department->designations()->withCount('users')->orderBy('level')->get();
        $contracts = $department->contracts()->with(['user.designation'])->latest('start_date')->take(10)->get();

        return view('departments.show', compact('department', 'employees', 'designations', 'contracts'));
    }

    public function edit(Request $request, Department $department): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $department->company);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $parentDepartments = Department::where('is_active', true)
            ->where('id', '!=', $department->id)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $heads = User::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('departments.edit', compact('department', 'companies', 'parentDepartments', 'heads'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $department->company);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_department_id' => ['nullable', 'exists:departments,id'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'is_active' => ['boolean'],
        ]);

        if (! $user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        if (! empty($validated['parent_department_id']) && $validated['parent_department_id'] == $department->id) {
            return back()->with('error', 'A department cannot be its own parent.')->withInput();
        }

        $department->update($validated);

        return redirect()->route('departments.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Department "'.$department->name.'" updated successfully.');
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $department->company);

        $hasUsers = $department->users()->count() > 0;
        $hasChildren = $department->childDepartments()->count() > 0;

        if ($hasUsers || $hasChildren) {
            $parts = [];
            if ($hasUsers) {
                $parts[] = $department->users()->count().' employee(s)';
            }
            if ($hasChildren) {
                $parts[] = $department->childDepartments()->count().' sub-department(s)';
            }

            return redirect()->route('departments.index')->with('error', 'Cannot delete "'.$department->name.'" — has '.implode(' and ', $parts).'.');
        }

        $name = $department->name;
        $department->delete();

        return redirect()->route('departments.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Department "'.$name.'" deleted.');
    }
}
