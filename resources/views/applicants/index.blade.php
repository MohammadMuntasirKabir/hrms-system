<x-layouts::app :title="__('Applicants')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Job Applicants') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Review and manage job applicants') }}</flux:subheading>
            </div>
            @can('applicants.create')
                <flux:button :href="route('applicants.create')" variant="primary" icon="plus">{{ __('Add Applicant') }}</flux:button>
            @endcan
        </div>

        <!-- Status Stats -->
        <div class="flex flex-wrap gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</span>
                <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300">{{ $statusCounts['pending'] }}</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ __('Reviewing') }}</span>
                <span class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ $statusCounts['reviewing'] }}</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400">{{ __('Shortlisted') }}</span>
                <span class="text-sm font-bold text-purple-700 dark:text-purple-300">{{ $statusCounts['shortlisted'] }}</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ __('Hired') }}</span>
                <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ $statusCounts['hired'] }}</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/20">
                <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ __('Rejected') }}</span>
                <span class="text-sm font-bold text-red-700 dark:text-red-300">{{ $statusCounts['rejected'] }}</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('applicants.index')" size="sm" variant="{{ !$filterStatus && !$filterDepartment && !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All') }}</flux:button>
            @foreach (['pending', 'reviewing', 'shortlisted', 'hired', 'rejected'] as $status)
                <flux:button :href="route('applicants.index', array_merge(request()->query(), ['status' => $status]))" size="sm" variant="{{ $filterStatus === $status ? 'primary' : 'outline' }}" wire:navigate>{{ ucfirst($status) }}</flux:button>
            @endforeach

            @if($currentUser->isSuperAdmin() && $companies->count() > 1)
                <span class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1 self-center"></span>
                @foreach ($companies as $company)
                    <flux:button :href="route('applicants.index', array_merge(request()->query(), ['company_id' => $company->id]))" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            @endif

            @if($departments->count() > 0)
                <span class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1 self-center"></span>
                @foreach ($departments as $dept)
                    <flux:button :href="route('applicants.index', array_merge(request()->query(), ['department_id' => $dept->id]))" size="sm" variant="{{ $filterDepartment == $dept->id ? 'primary' : 'outline' }}" wire:navigate>{{ $dept->name }}</flux:button>
                @endforeach
            @endif
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 p-4 flex items-center gap-3">
                <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 flex items-center gap-3">
                <flux:icon name="exclamation-circle" class="size-5 text-red-600 dark:text-red-400 shrink-0" />
                <span class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</span>
            </div>
        @endif

        <div class="hrms-card flex-1 min-h-0">
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Applicant') }}</flux:table.column>
                        <flux:table.column>{{ __('Position') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column>{{ __('Expected Salary') }}</flux:table.column>
                        <flux:table.column>{{ __('Applied') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($applicants as $applicant)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar :name="$applicant->full_name" size="sm" />
                                        <div>
                                            <a href="{{ route('applicants.show', $applicant) }}" class="font-medium text-hrms-600 dark:text-hrms-400 hover:underline" wire:navigate>{{ $applicant->full_name }}</a>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $applicant->email }}</div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($applicant->designation)
                                        <span class="hrms-badge-info">{{ $applicant->designation->title }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $applicant->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell class="text-sm">
                                    @if ($applicant->expected_salary)
                                        {{ $applicant->currency }} {{ number_format($applicant->expected_salary, 0) }}
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-sm text-zinc-500 dark:text-zinc-400">{{ $applicant->created_at->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $statusColors = ['pending' => 'warning', 'reviewing' => 'info', 'shortlisted' => 'purple', 'hired' => 'success', 'rejected' => 'danger'];
                                    @endphp
                                    <span class="hrms-badge-{{ $statusColors[$applicant->status] ?? 'neutral' }}">{{ ucfirst($applicant->status) }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="hrms-actions justify-end">
                                        @if ($applicant->isRejected())
                                            {{-- Rejected: Undo or Permanently Delete --}}
                                            <form method="POST" action="{{ route('applicants.undo-reject', $applicant) }}" class="inline">
                                                @csrf
                                                <flux:button type="submit" size="xs" variant="outline" icon="arrow-uturn-left" title="{{ __('Undo Rejection') }}">{{ __('Undo') }}</flux:button>
                                            </form>
                                            <form method="POST" action="{{ route('applicants.force-delete', $applicant) }}" class="inline">
                                                @csrf @method('DELETE')
                                                <flux:button type="submit" size="xs" variant="danger" icon="trash" title="{{ __('Permanently Delete') }}" onclick="return confirm('{{ __('Permanently delete this applicant? This cannot be undone.') }}')"></flux:button>
                                            </form>
                                        @elseif ($applicant->isHired())
                                            {{-- Hired: Undo (within 24h) or View Employee --}}
                                            @if ($applicant->updated_at->gte(now()->subDay()))
                                                <form method="POST" action="{{ route('applicants.undo-hire', $applicant) }}" class="inline">
                                                    @csrf
                                                    <flux:button type="submit" size="xs" variant="outline" icon="arrow-uturn-left" title="{{ __('Undo Hire') }}" onclick="return confirm('{{ __('Undo this hire? The employee record will be deleted.') }}')">{{ __('Undo Hire') }}</flux:button>
                                                </form>
                                            @endif
                                            @if ($applicant->hiredAsUser)
                                                <flux:button :href="route('users.show', $applicant->hiredAsUser)" size="xs" variant="success" icon="user" wire:navigate>{{ __('View Employee') }}</flux:button>
                                            @endif
                                        @else
                                            {{-- Pending / Reviewing / Shortlisted: Review, Shortlist, View --}}
                                            @if (auth()->user()->isSeniorMember())
                                                @if ($applicant->isPending())
                                                    <form method="POST" action="{{ route('applicants.review', $applicant) }}" class="inline">
                                                        @csrf
                                                        <flux:button type="submit" size="xs" variant="outline" icon="eye" title="{{ __('Mark as Reviewing') }}"></flux:button>
                                                    </form>
                                                @endif
                                                @if (in_array($applicant->status, ['pending', 'reviewing']))
                                                    <form method="POST" action="{{ route('applicants.shortlist', $applicant) }}" class="inline">
                                                        @csrf
                                                        <flux:button type="submit" size="xs" variant="primary" icon="star" title="{{ __('Shortlist') }}"></flux:button>
                                                    </form>
                                                @endif
                                            @endif
                                            <flux:button :href="route('applicants.show', $applicant)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon"><flux:icon name="user-plus" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">
                                            @if ($filterStatus === 'rejected')
                                                {{ __('No rejected applicants.') }}
                                            @elseif ($filterStatus === 'hired')
                                                {{ __('No recently hired applicants. When you hire someone, they appear here for 24 hours so you can undo if needed.') }}
                                            @else
                                                {{ __('No applicants found.') }}
                                            @endif
                                        </flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">{{ $applicants->links() }}</div>
    </div>
</x-layouts::app>
