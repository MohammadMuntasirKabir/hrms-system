<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    private function authorizeCompany(Request $request, Company $company): void
    {
        if (!$request->user()->canViewCompany($company)) {
            abort(403);
        }
    }

    /**
     * Get the active company filter from query or session.
     */
    private function getCompanyFilter(Request $request): ?int
    {
        $companyId = $request->input('company_id') ?? session('filter_company_id');
        return $companyId ? (int) $companyId : null;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $this->getCompanyFilter($request);

        $query = Contract::with(['user.designation', 'user.department', 'company', 'department']);

        if (!$user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'expired', 'terminated', 'draft'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $contracts = $query->orderByDesc('start_date')->paginate(20);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        $departments = Department::where('is_active', true)
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('contracts.index', [
            'contracts' => $contracts,
            'currentUser' => $user,
            'companies' => $companies,
            'departments' => $departments,
            'filterCompany' => $companyId,
            'filterStatus' => $request->status,
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

        $users = User::where('is_active', true)
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('contracts.create', compact('companies', 'departments', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'contract_type' => ['required', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'status' => ['required', 'string', 'in:active,draft,terminated'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $this->authorizeCompany($request, $company);

        if (!$user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        $contract = Contract::create($validated);

        return redirect()->route('contracts.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Contract for ' . $contract->user->name . ' created successfully.');
    }

    public function show(Request $request, Contract $contract): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $contract->company);

        $contract->load(['user.designation', 'user.department', 'company', 'department']);

        return view('contracts.show', ['contract' => $contract]);
    }

    public function edit(Request $request, Contract $contract): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $contract->company);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $departments = Department::where('is_active', true)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $users = User::where('is_active', true)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('contracts.edit', compact('contract', 'companies', 'departments', 'users'));
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $contract->company);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'contract_type' => ['required', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'status' => ['required', 'string', 'in:active,draft,terminated,expired'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!$user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        $contract->update($validated);

        return redirect()->route('contracts.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Contract for ' . $contract->user->name . ' updated successfully.');
    }

    public function destroy(Request $request, Contract $contract): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $contract->company);

        $userName = $contract->user->name;
        $contract->delete();

        return redirect()->route('contracts.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Contract for ' . $userName . ' deleted.');
    }
}
