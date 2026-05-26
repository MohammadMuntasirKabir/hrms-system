<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Company::withCount(['users', 'departments', 'designations', 'contracts']);

        if (! $user->isSuperAdmin()) {
            $query->whereIn('id', $user->getAllowedCompanyIds());
        }

        $companies = $query->with('childCompanies')->orderBy('name')->paginate(20);

        return view('companies.index', [
            'companies' => $companies,
            'currentUser' => $user,
        ]);
    }

    public function create(Request $request): View
    {
        return view('companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies'],
            'slug' => ['required', 'string', 'max:255', 'unique:companies'],
            'domain' => ['nullable', 'string', 'max:255'],
            'parent_company_id' => ['nullable', 'exists:companies,id'],
            'country' => ['nullable', 'string', 'max:2'],
            'timezone' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['is_active'] = true;
        $company = Company::create($validated);

        return redirect()->route('companies.index')
            ->with('status', 'Company "'.$company->name.'" created successfully.');
    }

    public function show(Request $request, Company $company): View
    {
        $user = $request->user();

        if (! $user->canViewCompany($company)) {
            abort(403);
        }

        $company->load(['childCompanies'])
            ->loadCount(['departments', 'designations', 'contracts', 'users']);

        $departments = $company->departments()
            ->withCount(['users', 'designations', 'contracts'])
            ->orderBy('name')
            ->get();

        $designations = $company->designations()
            ->withCount('users')
            ->orderBy('level')
            ->take(10)
            ->get();

        $contracts = $company->contracts()
            ->with(['user.designation'])
            ->latest('start_date')
            ->take(10)
            ->get();

        return view('companies.show', [
            'company' => $company,
            'departments' => $departments,
            'designations' => $designations,
            'contracts' => $contracts,
        ]);
    }

    public function edit(Request $request, Company $company): View
    {
        $companies = Company::where('id', '!=', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('companies.edit', [
            'company' => $company,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name,'.$company->id],
            'slug' => ['required', 'string', 'max:255', 'unique:companies,slug,'.$company->id],
            'domain' => ['nullable', 'string', 'max:255'],
            'parent_company_id' => ['nullable', 'exists:companies,id'],
            'country' => ['nullable', 'string', 'max:2'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['parent_company_id']) && $validated['parent_company_id'] == $company->id) {
            return back()->with('error', 'A company cannot be its own parent.')->withInput();
        }

        $company->update($validated);

        return redirect()->route('companies.show', $company)
            ->with('status', 'Company "'.$company->name.'" updated successfully.');
    }

    public function destroy(Request $request, Company $company): RedirectResponse
    {
        $hasUsers = $company->users()->count() > 0;
        $hasChildren = $company->childCompanies()->count() > 0;
        $hasDepartments = $company->departments()->count() > 0;

        if ($hasUsers || $hasChildren || $hasDepartments) {
            $parts = [];
            if ($hasUsers) {
                $parts[] = $company->users()->count().' employee(s)';
            }
            if ($hasChildren) {
                $parts[] = $company->childCompanies()->count().' sub-company(s)';
            }
            if ($hasDepartments) {
                $parts[] = $company->departments()->count().' department(s)';
            }

            return redirect()->route('companies.index')
                ->with('error', 'Cannot delete "'.$company->name.'" — has '.implode(', ', $parts).'.');
        }

        $name = $company->name;
        $company->delete();

        return redirect()->route('companies.index')
            ->with('status', 'Company "'.$name.'" deleted.');
    }
}
