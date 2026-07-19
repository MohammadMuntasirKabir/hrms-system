<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Department Head Dashboard') }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Company: :name', ['name' => auth()->user()->company?->name ?? '—']) }}</flux:subheading>
        </div>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="users" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Team Members') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $team->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-amber-50 dark:bg-amber-900/30"><flux:icon name="clock" class="size-5 text-amber-600 dark:text-amber-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending Leave Requests') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $pendingLeaves->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="building-office" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Department') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->department?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($pendingLeaves->count() > 0)
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">{{ __('Pending Leave Requests') }}</flux:heading>
                        @can('leave.approve')
                            <flux:button :href="route('leaves.index', ['status' => 'pending'])" variant="outline" size="sm" wire:navigate icon="arrow-right">{{ __('Review') }}</flux:button>
                        @endcan
                    </div>
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Employee') }}</flux:table.column>
                                <flux:table.column>{{ __('Type') }}</flux:table.column>
                                <flux:table.column>{{ __('Period') }}</flux:table.column>
                                <flux:table.column class="text-center">{{ __('Days') }}</flux:table.column>
                                <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($pendingLeaves as $leave)
                                    <flux:table.row>
                                        <flux:table.cell>
                                            <div class="flex items-center gap-2">
                                                <flux:avatar :name="$leave->user->name" size="xs" />
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $leave->user->name }}</span>
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell><span class="hrms-badge-neutral">{{ ucfirst($leave->type) }}</span></flux:table.cell>
                                        <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $leave->start_date->format('M d') }} – {{ $leave->end_date->format('M d, Y') }}</flux:table.cell>
                                        <flux:table.cell class="text-center"><span class="hrms-badge-info">{{ $leave->total_days }}</span></flux:table.cell>
                                        <flux:table.cell class="text-right">
                                            <div class="hrms-actions justify-end">
                                                <flux:button :href="route('leaves.show', $leave)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            </div>
        @endif

        <div class="hrms-card flex-1 min-h-0">
            <div class="hrms-card-body">
                <flux:heading size="lg" level="2" class="mb-4 text-zinc-900 dark:text-white">{{ __('Team Overview') }}</flux:heading>
                @if ($team->count() > 0)
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Member') }}</flux:table.column>
                                <flux:table.column>{{ __('Designation') }}</flux:table.column>
                                <flux:table.column>{{ __('Status') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($team as $member)
                                    <flux:table.row>
                                        <flux:table.cell>
                                            <div class="flex items-center gap-2">
                                                <flux:avatar :name="$member->name" size="xs" />
                                                <a href="{{ route('users.show', $member) }}" class="font-medium text-hrms-600 dark:text-hrms-400 hover:underline" wire:navigate>{{ $member->name }}</a>
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $member->designation?->title ?? '—' }}</flux:table.cell>
                                        <flux:table.cell>
                                            @if ($member->is_active)<span class="hrms-badge-success">{{ __('Active') }}</span>
                                            @else<span class="hrms-badge-danger">{{ __('Inactive') }}</span>@endif
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <div class="hrms-empty-state py-8">
                        <div class="hrms-empty-state-icon"><flux:icon name="user-group" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No team members assigned to this department yet.') }}</flux:text>
                    </div>
                @endif
            </div>
        </div>

        @if ($expiringContracts->count() > 0)
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="lg" level="2" class="mb-4 text-zinc-900 dark:text-white">{{ __('Contracts Expiring Soon') }}</flux:heading>
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Employee') }}</flux:table.column>
                                <flux:table.column>{{ __('Ends') }}</flux:table.column>
                                <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($expiringContracts as $contract)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ $contract->user?->name ?? '—' }}</flux:table.cell>
                                        <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $contract->end_date->format('M d, Y') }}</flux:table.cell>
                                        <flux:table.cell class="text-right">
                                            <div class="hrms-actions justify-end">
                                                <flux:button :href="route('contracts.show', $contract)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
