<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
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

        $query = Leave::query()->with(['user.department', 'company', 'approver']);

        if (! $user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Employees only see their own leave requests
        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $leaves = $query->orderByDesc('created_at')->paginate(20);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        return view('leaves.index', [
            'leaves' => $leaves,
            'currentUser' => $user,
            'companies' => $companies,
            'filterCompany' => $companyId,
            'statusFilter' => $request->input('status'),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $employees = User::query()
            ->where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $user->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('leaves.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'string', 'in:'.implode(',', Leave::TYPES)],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = User::findOrFail($validated['user_id']);
        $this->authorizeCompany($request, $employee->company);

        // Non-admin users may only request leave for themselves
        if ($user->isEmployee()) {
            $validated['user_id'] = $user->id;
            $employee = $user;
        }

        $validated['company_id'] = $employee->company_id;
        $validated['total_days'] = (int) now()->parse($validated['start_date'])
            ->diffInDays(now()->parse($validated['end_date'])) + 1;
        $validated['status'] = 'pending';

        $leave = Leave::create($validated);

        return redirect()->route('leaves.index')
            ->with('status', 'Leave request submitted for "'.$employee->name.'".');
    }

    public function show(Request $request, Leave $leave): View
    {
        $request->user()->canViewCompany($leave->company) || abort(403);

        $leave->load(['user.department', 'company', 'approver']);

        return view('leaves.show', compact('leave'));
    }

    public function edit(Request $request, Leave $leave): View
    {
        $request->user()->canViewCompany($leave->company) || abort(403);

        if (! $leave->isPending) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only pending leave requests can be edited.');
        }

        // Non-managers may only edit their own leave requests
        if (! $request->user()->can('leave.manage') && $leave->user_id !== $request->user()->id) {
            abort(403);
        }

        $employees = User::query()
            ->where('is_active', true)
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->whereIn('company_id', $request->user()->getAllowedCompanyIds()))
            ->orderBy('name')
            ->get();

        return view('leaves.edit', compact('leave', 'employees'));
    }

    public function update(Request $request, Leave $leave): RedirectResponse
    {
        $request->user()->canViewCompany($leave->company) || abort(403);

        if (! $leave->isPending) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only pending leave requests can be edited.');
        }

        // Non-managers may only edit their own leave requests
        if (! $request->user()->can('leave.manage') && $leave->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'string', 'in:'.implode(',', Leave::TYPES)],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = User::findOrFail($validated['user_id']);
        $this->authorizeCompany($request, $employee->company);

        if ($request->user()->isEmployee()) {
            $validated['user_id'] = $request->user()->id;
        }

        $validated['total_days'] = (int) now()->parse($validated['start_date'])
            ->diffInDays(now()->parse($validated['end_date'])) + 1;

        $leave->update($validated);

        return redirect()->route('leaves.show', $leave)
            ->with('status', 'Leave request updated.');
    }

    public function destroy(Request $request, Leave $leave): RedirectResponse
    {
        $request->user()->canViewCompany($leave->company) || abort(403);

        // Non-managers may only delete their own leave requests
        if (! $request->user()->can('leave.manage') && $leave->user_id !== $request->user()->id) {
            abort(403);
        }

        $leave->delete();

        return redirect()->route('leaves.index')
            ->with('status', 'Leave request deleted.');
    }

    public function approve(Request $request, Leave $leave): RedirectResponse
    {
        $request->user()->canViewCompany($leave->company) || abort(403);
        $request->user()->can('leave.approve') || abort(403);

        if (! $leave->isPending) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only pending leave requests can be approved.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'decided_at' => now(),
            'admin_note' => $request->input('admin_note'),
        ]);

        return redirect()->route('leaves.show', $leave)
            ->with('status', 'Leave request approved.');
    }

    public function reject(Request $request, Leave $leave): RedirectResponse
    {
        $request->user()->canViewCompany($leave->company) || abort(403);
        $request->user()->can('leave.approve') || abort(403);

        if (! $leave->isPending) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only pending leave requests can be rejected.');
        }

        $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'decided_at' => now(),
            'admin_note' => $request->input('admin_note'),
        ]);

        return redirect()->route('leaves.show', $leave)
            ->with('status', 'Leave request rejected.');
    }

    public function cancel(Request $request, Leave $leave): RedirectResponse
    {
        $request->user()->canViewCompany($leave->company) || abort(403);

        // Only the requester or someone with manage permission can cancel
        if ($leave->user_id !== $request->user()->id && ! $request->user()->can('leave.manage')) {
            abort(403);
        }

        if (! $leave->isPending) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leave->update([
            'status' => 'cancelled',
            'decided_at' => now(),
        ]);

        return redirect()->route('leaves.show', $leave)
            ->with('status', 'Leave request cancelled.');
    }
}
