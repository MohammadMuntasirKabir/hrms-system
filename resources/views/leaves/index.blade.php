<x-layouts::app :title="__('Leave Management')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Leave Management') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Track and approve employee leave requests') }}</flux:subheading>
            </div>
            @can('leave.manage')
                <flux:button :href="route('leaves.create')" variant="primary" icon="plus">{{ __('New Request') }}</flux:button>
            @endcan
        </div>

        @if($currentUser->isSuperAdmin() && $companies->count() > 1)
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('leaves.index')" size="sm" variant="{{ !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All Companies') }}</flux:button>
                @foreach ($companies as $company)
                    <flux:button :href="route('leaves.index', ['company_id' => $company->id])" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('leaves.index', $statusFilter ? ['status' => $statusFilter] : [])" size="sm" variant="outline" wire:navigate>{{ __('All') }}</flux:button>
            <flux:button :href="route('leaves.index', array_merge(request()->except('status') ?? [], ['status' => 'pending']))" size="sm" variant="{{ $statusFilter === 'pending' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Pending') }}</flux:button>
            <flux:button :href="route('leaves.index', array_merge(request()->except('status') ?? [], ['status' => 'approved']))" size="sm" variant="{{ $statusFilter === 'approved' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Approved') }}</flux:button>
            <flux:button :href="route('leaves.index', array_merge(request()->except('status') ?? [], ['status' => 'rejected']))" size="sm" variant="{{ $statusFilter === 'rejected' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Rejected') }}</flux:button>
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
                        <flux:table.column>{{ __('Employee') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Period') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Days') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($leaves as $leave)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        <flux:avatar :name="$leave->user->name" size="xs" />
                                        <span class="font-medium text-hrms-600 dark:text-hrms-400">{{ $leave->user->name }}</span>
                                    </div>
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $leave->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell><span class="hrms-badge-neutral">{{ ucfirst($leave->type) }}</span></flux:table.cell>
                                <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $leave->start_date->format('M d') }} – {{ $leave->end_date->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-info">{{ $leave->total_days }}</span></flux:table.cell>
                                <flux:table.cell class="text-center">
                                    @if ($leave->isPending)
                                        <span class="hrms-badge-warning">{{ __('Pending') }}</span>
                                    @elseif ($leave->isApproved)
                                        <span class="hrms-badge-success">{{ __('Approved') }}</span>
                                    @elseif ($leave->isRejected)
                                        <span class="hrms-badge-danger">{{ __('Rejected') }}</span>
                                    @else
                                        <span class="hrms-badge-neutral">{{ __('Cancelled') }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="hrms-actions justify-end">
                                        <flux:button :href="route('leaves.show', $leave)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                        @can('leave.manage')
                                            <flux:button :href="route('leaves.edit', $leave)" size="xs" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon">
                                            <flux:icon name="calendar" class="size-7 text-zinc-400 dark:text-zinc-600" />
                                        </div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No leave requests yet.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">
            {{ $leaves->links() }}
        </div>
    </div>
</x-layouts::app>
