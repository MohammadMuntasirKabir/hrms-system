<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignationController extends Controller
{
    private function authorizeCompany(Request $request, Company $company): void
    {
        if (!$request->user()->canViewCompany($company)) {
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

        $query = Designation::with(['company', 'department'])->withCount('users');

        if (!$user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $designations = $query->orderBy('level')->orderBy('title')->paginate(20);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        $departments = Department::where('is_active', true)
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('designations.index', [
            'designations' => $designations,
            'currentUser' => $user,
            'companies' => $companies,
            'departments' => $departments,
            'filterCompany' => $companyId,
            'filterDepartment' => $request->department_id,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $companyId = $this->getCompanyFilter($request);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $departments = Department::where('is_active', true)
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('designations.create', compact('companies', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'level' => ['required', 'integer', 'min:0', 'max:10'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $this->authorizeCompany($request, $company);

        if (! $user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        $validated['is_active'] = true;
        $designation = Designation::create($validated);

        return redirect()->route('designations.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Designation "' . $designation->title . '" created successfully.');
    }

    public function show(Request $request, Designation $designation): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $designation->company);

        $designation->load(['company', 'department'])->loadCount('users');

        return view('designations.show', [
            'designation' => $designation,
            'currentUser' => $user,
        ]);
    }

    public function edit(Request $request, Designation $designation): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $designation->company);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $departments = Department::where('is_active', true)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('designations.edit', compact('designation', 'companies', 'departments'));
    }

    public function update(Request $request, Designation $designation): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $designation->company);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'level' => ['required', 'integer', 'min:0', 'max:10'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'is_active' => ['boolean'],
        ]);

        if (!$user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        $designation->update($validated);
        return redirect()->route('designations.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Designation "' . $designation->title . '" updated successfully.');
    }

    public function destroy(Request $request, Designation $designation): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $designation->company);

        if ($designation->users()->count() > 0) {
            return redirect()->route('designations.index')->with('error', 'Cannot delete "' . $designation->title . '" — assigned to ' . $designation->users()->count() . ' employee(s).');
        }

        $title = $designation->title;
        $designation->delete();
        return redirect()->route('designations.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Designation "' . $title . '" deleted.');
    }
}
