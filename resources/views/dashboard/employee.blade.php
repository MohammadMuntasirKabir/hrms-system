<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __("Here's what's happening across your organization today.") }}</flux:subheading>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="building-office" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Company') }}</flux:text>
                            <p class="text-xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->company?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="identification" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Employee ID') }}</flux:text>
                            <p class="text-xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->employee_id ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30"><flux:icon name="user-group" class="size-5 text-emerald-600 dark:text-emerald-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Role') }}</flux:text>
                            <p class="text-xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->getRoleNames()->first() ? ucwords(str_replace('_', ' ', auth()->user()->getRoleNames()->first())) : '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 flex-1 min-h-0">
            <!-- Active Contract -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="lg" level="2" class="mb-4 text-zinc-900 dark:text-white">{{ __('My Active Contract') }}</flux:heading>
                    @if ($activeContract)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Position') }}</flux:text>
                                <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $activeContract->position }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Type') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ ucwords(str_replace('_', ' ', $activeContract->contract_type)) }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Start Date') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $activeContract->start_date->format('M d, Y') }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('End Date') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $activeContract->end_date ? $activeContract->end_date->format('M d, Y') : '—' }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <flux:button :href="route('contracts.show', $activeContract)" variant="outline" size="sm" icon="eye" wire:navigate>{{ __('View Contract') }}</flux:button>
                        </div>
                    @else
                        <div class="hrms-empty-state py-8">
                            <div class="hrms-empty-state-icon"><flux:icon name="document-text" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('You don\'t have an active contract yet.') }}</flux:text>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Leave Summary -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">{{ __('My Leave') }}</flux:heading>
                        @can('leave.manage')
                            <flux:button :href="route('leaves.create')" variant="outline" size="sm" wire:navigate icon="plus">{{ __('New Request') }}</flux:button>
                        @endcan
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 p-3 text-center">
                            <p class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ $leaveSummary['pending'] }}</p>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</flux:text>
                        </div>
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center">
                            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ $leaveSummary['approved'] }}</p>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Approved') }}</flux:text>
                        </div>
                        <div class="rounded-lg bg-zinc-100 dark:bg-zinc-800 p-3 text-center">
                            <p class="text-xl font-bold text-zinc-700 dark:text-zinc-300">{{ $leaveSummary['total'] }}</p>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total') }}</flux:text>
                        </div>
                    </div>
                    @if ($myLeaves->count() > 0)
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                                    <flux:table.column>{{ __('Period') }}</flux:table.column>
                                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($myLeaves as $leave)
                                        <flux:table.row>
                                            <flux:table.cell><span class="hrms-badge-neutral">{{ ucfirst($leave->type) }}</span></flux:table.cell>
                                            <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $leave->start_date->format('M d') }} – {{ $leave->end_date->format('M d') }}</flux:table.cell>
                                            <flux:table.cell>
                                                @if ($leave->isPending)<span class="hrms-badge-warning">{{ __('Pending') }}</span>
                                                @elseif ($leave->isApproved)<span class="hrms-badge-success">{{ __('Approved') }}</span>
                                                @elseif ($leave->isRejected)<span class="hrms-badge-danger">{{ __('Rejected') }}</span>
                                                @else<span class="hrms-badge-neutral">{{ __('Cancelled') }}</span>@endif
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    @else
                        <div class="hrms-empty-state py-6">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No leave requests yet.') }}</flux:text>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
