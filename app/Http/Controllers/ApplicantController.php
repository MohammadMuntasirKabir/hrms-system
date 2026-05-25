<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ApplicantController extends Controller
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

        $query = JobApplicant::with(['company', 'department', 'designation']);

        if (!$user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('status') && in_array($request->status, ['pending', 'reviewing', 'shortlisted', 'hired', 'rejected'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $applicants = $query->orderByDesc('created_at')->paginate(20);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        $departments = Department::where('is_active', true)
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $statusCounts = [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'reviewing' => (clone $query)->where('status', 'reviewing')->count(),
            'shortlisted' => (clone $query)->where('status', 'shortlisted')->count(),
            'hired' => (clone $query)->where('status', 'hired')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];

        return view('applicants.index', [
            'applicants' => $applicants,
            'currentUser' => $user,
            'companies' => $companies,
            'departments' => $departments,
            'filterCompany' => $companyId,
            'filterStatus' => $request->status,
            'filterDepartment' => $request->department_id,
            'statusCounts' => $statusCounts,
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

        $designations = Designation::where('is_active', true)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('title')
            ->get();

        return view('applicants.create', compact('companies', 'departments', 'designations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:job_applicants'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:2'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'string', 'max:100'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'available_from' => ['nullable', 'date'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $this->authorizeCompany($request, $company);

        if (!$user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        $validated['status'] = 'pending';
        $validated['currency'] = $validated['currency'] ?? 'BDT';

        $applicant = JobApplicant::create($validated);

        return redirect()->route('applicants.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Applicant ' . $applicant->full_name . ' added successfully.');
    }

    public function show(Request $request, JobApplicant $applicant): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $applicant->company);

        $applicant->load(['company', 'department', 'designation', 'reviewer', 'hiredAsUser']);

        return view('applicants.show', [
            'applicant' => $applicant,
            'currentUser' => $user,
        ]);
    }

    public function edit(Request $request, JobApplicant $applicant): View
    {
        $user = $request->user();
        $this->authorizeCompany($request, $applicant->company);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : Company::whereIn('id', $user->getAllowedCompanyIds())->orderBy('name')->get();

        $departments = Department::where('is_active', true)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        $designations = Designation::where('is_active', true)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('title')
            ->get();

        return view('applicants.edit', compact('applicant', 'companies', 'departments', 'designations'));
    }

    public function update(Request $request, JobApplicant $applicant): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $applicant->company);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:job_applicants,email,' . $applicant->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:2'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'string', 'max:100'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'available_from' => ['nullable', 'date'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'status' => ['required', 'string', 'in:pending,reviewing,shortlisted,hired,rejected'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!$user->isSuperAdmin()) {
            $validated['company_id'] = $user->company_id;
        }

        // If status changed to reviewing, set reviewer info
        if ($validated['status'] === 'reviewing' && $applicant->status !== 'reviewing') {
            $validated['reviewed_by'] = $user->id;
            $validated['reviewed_at'] = now();
        }

        $applicant->update($validated);

        return redirect()->route('applicants.show', $applicant)
            ->with('status', 'Applicant ' . $applicant->full_name . ' updated successfully.');
    }

    public function destroy(Request $request, JobApplicant $applicant): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $applicant->company);

        $name = $applicant->full_name;
        $applicant->delete();

        return redirect()->route('applicants.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Applicant ' . $name . ' deleted.');
    }

    /**
     * Hire an applicant — creates a full employee record with all relationships.
     */
    public function hire(Request $request, JobApplicant $applicant): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $applicant->company);

        if ($applicant->isHired()) {
            return redirect()->route('applicants.show', $applicant)
                ->with('error', 'This applicant has already been hired.');
        }

        $validated = $request->validate([
            'employee_id' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['required', 'string', 'in:full_time,part_time,contract,internship,freelance'],
            'position' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'role' => ['required', 'string', 'in:hr_executive,department_head,employee'],
        ]);

        // Generate employee ID if not provided
        $employeeId = $validated['employee_id'] ?? $this->generateEmployeeId($applicant->company_id);

        // Generate a random password
        $password = bin2hex(random_bytes(8));

        // Create the employee user
        $employee = User::create([
            'name' => $applicant->full_name,
            'email' => $applicant->email,
            'password' => Hash::make($password),
            'company_id' => $applicant->company_id,
            'department_id' => $applicant->department_id,
            'designation_id' => $applicant->designation_id,
            'employee_id' => $employeeId,
            'job_title' => $validated['job_title'] ?? $applicant->designation?->title,
            'is_active' => true,
        ]);

        $employee->assignRole($validated['role']);

        // Create contract
        $contract = Contract::create([
            'user_id' => $employee->id,
            'company_id' => $applicant->company_id,
            'department_id' => $applicant->department_id,
            'contract_type' => $validated['contract_type'],
            'position' => $validated['position'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'salary' => $validated['salary'] ?? $applicant->expected_salary,
            'currency' => $applicant->currency,
            'status' => 'active',
        ]);

        // Create salary record if salary provided
        if (!empty($validated['salary'])) {
            Salary::create([
                'user_id' => $employee->id,
                'company_id' => $applicant->company_id,
                'department_id' => $applicant->department_id,
                'designation_id' => $applicant->designation_id,
                'contract_id' => $contract->id,
                'base_salary' => $validated['salary'],
                'allowances' => 0,
                'deductions' => 0,
                'net_salary' => $validated['salary'],
                'currency' => $applicant->currency,
                'pay_frequency' => 'monthly',
                'effective_from' => $validated['start_date'],
                'status' => 'active',
                'created_by' => $user->id,
            ]);
        }

        // Update applicant status
        $applicant->update([
            'status' => 'hired',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'hired_as_user_id' => $employee->id,
        ]);

        return redirect()->route('users.show', $employee)
            ->with('status', 'Applicant ' . $applicant->full_name . ' has been hired as ' . $employee->name . '. A new employee record has been created with contract and salary.');
    }

    /**
     * Reject an applicant.
     */
    public function reject(Request $request, JobApplicant $applicant): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeCompany($request, $applicant->company);

        if ($applicant->isHired()) {
            return redirect()->route('applicants.show', $applicant)
                ->with('error', 'Cannot reject an already hired applicant.');
        }

        $applicant->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('applicants.index', $this->getCompanyFilter($request) ? ['company_id' => $this->getCompanyFilter($request)] : [])
            ->with('status', 'Applicant ' . $applicant->full_name . ' has been rejected.');
    }

    private function generateEmployeeId(int $companyId): string
    {
        $lastEmployee = User::where('company_id', $companyId)
            ->whereNotNull('employee_id')
            ->orderByDesc('id')
            ->first();

        $next = 1;
        if ($lastEmployee && preg_match('/(\d+)$/', $lastEmployee->employee_id, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return 'EMP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
