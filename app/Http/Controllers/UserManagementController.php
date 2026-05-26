<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
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

        $query = User::with(['company', 'department', 'designation'])->withCount('contracts');

        if (! $user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->paginate(20);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        $departments = Department::where('is_active', true)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('users.index', [
            'users' => $users,
            'currentUser' => $user,
            'companies' => $companies,
            'departments' => $departments,
            'filterCompany' => $companyId,
            'filterDepartment' => $request->department_id,
            'filterStatus' => $request->status,
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
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $designations = Designation::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('level')
            ->orderBy('title')
            ->get();

        $roles = $this->getAssignableRoles($user);

        return view('users.create', compact('companies', 'departments', 'designations', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'role' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $this->authorizeCompany($request, $company);

        if (! $user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'company_id' => $validated['company_id'],
            'department_id' => $validated['department_id'] ?? null,
            'designation_id' => $validated['designation_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $newUser->assignRole($validated['role']);

        return redirect()->route('users.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'User '.$newUser->name.' created successfully.');
    }

    public function show(Request $request, User $user): View
    {
        $currentUser = $request->user();

        if (! $currentUser->canViewCompany($user->company)) {
            abort(403);
        }

        $user->load(['company', 'department', 'designation', 'contracts', 'salaries']);

        return view('users.show', [
            'user' => $user,
            'currentUser' => $currentUser,
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        $currentUser = $request->user();

        if (! $currentUser->canViewCompany($user->company)) {
            abort(403);
        }

        if (! $currentUser->isSeniorMember() && $currentUser->id !== $user->id) {
            abort(403);
        }

        $companies = $currentUser->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $currentUser->getAllowedCompanyIds())->orderBy('name')->get();

        $departments = Department::where('is_active', true)
            ->when(! $currentUser->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $currentUser->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $designations = Designation::where('is_active', true)
            ->when(! $currentUser->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $currentUser->getAllowedCompanyIds()))
            ->orderBy('level')
            ->orderBy('title')
            ->get();

        $roles = $this->getAssignableRoles($currentUser);

        return view('users.edit', compact('user', 'companies', 'departments', 'designations', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->canViewCompany($user->company)) {
            abort(403);
        }

        if (! $currentUser->isSeniorMember() && $currentUser->id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'role' => ['required', 'string'],
            'is_active' => ['boolean'],
        ]);

        if (! $currentUser->isSuperAdmin()) {
            $validated['company_id'] = $currentUser->company_id;
        }

        if (! $currentUser->isSeniorMember()) {
            unset($validated['role']);
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'company_id' => $validated['company_id'],
            'department_id' => $validated['department_id'] ?? null,
            'designation_id' => $validated['designation_id'] ?? null,
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8', 'confirmed']]);
            $updateData['password'] = Hash::make($request->input('password'));
        }

        $user->update($updateData);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('users.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'User '.$user->name.' updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSeniorMember()) {
            abort(403);
        }

        if (! $currentUser->canViewCompany($user->company)) {
            abort(403);
        }

        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        Contract::where('user_id', $user->id)->delete();
        Salary::where('user_id', $user->id)->delete();

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'User '.$name.' deleted.');
    }

    private function getAssignableRoles(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return [
                'super_admin' => 'Super Admin',
                'company_admin' => 'Company Admin',
                'hr_manager' => 'HR Manager',
                'hr_executive' => 'HR Executive',
                'department_head' => 'Department Head',
                'employee' => 'Employee',
            ];
        }

        if ($user->isCompanyAdmin()) {
            return [
                'hr_manager' => 'HR Manager',
                'hr_executive' => 'HR Executive',
                'department_head' => 'Department Head',
                'employee' => 'Employee',
            ];
        }

        if ($user->hasRole('hr_manager')) {
            return [
                'hr_executive' => 'HR Executive',
                'department_head' => 'Department Head',
                'employee' => 'Employee',
            ];
        }

        return ['employee' => 'Employee'];
    }

    // === Admin Management (Super Admin Only) ===

    public function transferSuperAdmin(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Only a super admin can transfer super admin status.');
        }

        $validated = $request->validate([
            'new_superadmin_id' => ['required', 'exists:users,id'],
        ]);

        $newSuperAdmin = User::findOrFail($validated['new_superadmin_id']);

        // Must be an active user
        if (! $newSuperAdmin->is_active) {
            return back()->with('error', 'Cannot assign super admin to an inactive user.');
        }

        // Remove super admin from current user
        $currentUser->removeRole('super_admin');

        // Assign super admin to the new user
        $newSuperAdmin->assignRole('super_admin');

        return redirect()->route('users.index')
            ->with('status', 'Super admin role transferred to '.$newSuperAdmin->name.'. You are no longer a super admin.');
    }

    public function assignCompanyAdmin(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Only a super admin can assign company admins.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        // Must be an active user
        if (! $targetUser->is_active) {
            return back()->with('error', 'Cannot assign company admin to an inactive user.');
        }

        $companyId = $targetUser->company_id;

        if (! $companyId) {
            return back()->with('error', 'User must belong to a company to be assigned as company admin.');
        }

        // Check if this company already has a company admin (other than the target user)
        $existingAdmin = User::where('company_id', $companyId)
            ->where('id', '!=', $targetUser->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'company_admin'))
            ->first();

        if ($existingAdmin) {
            return back()->with('error', 'Company "'.$targetUser->company->name.'" already has an admin ('.$existingAdmin->name.'). Remove their admin status first.');
        }

        // If target already has company_admin, nothing to do
        if ($targetUser->isCompanyAdmin()) {
            return back()->with('status', $targetUser->name.' is already the admin of '.$targetUser->company->name.'.');
        }

        // Remove any existing senior role and assign company_admin
        $targetUser->removeRole('hr_manager');
        $targetUser->removeRole('hr_executive');
        $targetUser->removeRole('department_head');
        $targetUser->removeRole('employee');
        $targetUser->assignRole('company_admin');

        return redirect()->route('users.index')
            ->with('status', $targetUser->name.' is now the admin of '.$targetUser->company->name.'.');
    }

    public function removeCompanyAdmin(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Only a super admin can remove company admins.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        if (! $targetUser->isCompanyAdmin()) {
            return back()->with('error', $targetUser->name.' is not a company admin.');
        }

        $targetUser->removeRole('company_admin');
        $targetUser->assignRole('employee');

        return redirect()->route('users.index')
            ->with('status', $targetUser->name.' has been removed as admin of '.$targetUser->company->name.'. They are now an employee.');
    }
}
