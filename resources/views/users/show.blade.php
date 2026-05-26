<x-layouts::app :title="$user->name">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('users.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $user->name }}</flux:heading>
                    @if ($user->is_active)
                        <span class="hrms-badge-success">{{ __('Active') }}</span>
                    @else
                        <span class="hrms-badge-danger">{{ __('Inactive') }}</span>
                    @endif
                </div>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ $user->email }} · {{ $user->company?->name }}</flux:subheading>
            </div>
            <div class="hrms-actions">
                @if ($currentUser->can('users.edit'))
                    <flux:button :href="route('users.edit', $user)" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                @endif
            </div>
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

        <!-- Stats -->
        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="building-office" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Company') }}</flux:text>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $user->company?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="building-office-2" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Department') }}</flux:text>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $user->department?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-amber-50 dark:bg-amber-900/30"><flux:icon name="briefcase" class="size-5 text-amber-600 dark:text-amber-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Designation') }}</flux:text>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $user->designation?->title ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30"><flux:icon name="document-text" class="size-5 text-emerald-600 dark:text-emerald-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Contracts') }}</flux:text>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $user->contracts->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 flex-1 min-h-0">
            <!-- Employee Info -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Employee Information') }}</flux:heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Full Name') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Email') }}</flux:text>
                            <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $user->email }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Employee ID') }}</flux:text>
                            <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $user->employee_id ?? '—' }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Job Title') }}</flux:text>
                            <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $user->job_title ?? '—' }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Role') }}</flux:text>
                            <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ ucwords(str_replace('_', ' ', $user->getRoleNames()->first() ?? '—')) }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Status') }}</flux:text>
                            <p class="mt-1.5">
                                @if ($user->is_active)
                                    <span class="hrms-badge-success">{{ __('Active') }}</span>
                                @else
                                    <span class="hrms-badge-danger">{{ __('Inactive') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Contracts') }}</flux:heading>
                    @forelse ($user->contracts as $contract)
                        <div class="hrms-list-row flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $contract->position }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ ucwords(str_replace('_', ' ', $contract->contract_type)) }} · {{ $contract->start_date->format('M d, Y') }}</div>
                            </div>
                            <div class="hrms-actions">
                                @if ($contract->status === 'active')
                                    <span class="hrms-badge-success text-xs px-2 py-0.5">{{ __('Active') }}</span>
                                @elseif ($contract->is_expired)
                                    <span class="hrms-badge-danger text-xs px-2 py-0.5">{{ __('Expired') }}</span>
                                @else
                                    <span class="hrms-badge-neutral text-xs px-2 py-0.5">{{ ucfirst($contract->status) }}</span>
                                @endif
                                <flux:button :href="route('contracts.show', $contract)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-zinc-400 text-sm">{{ __('No contracts.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Salaries -->
        @if ($currentUser->canViewSalaries() && $user->salaries->count() > 0)
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Salary History') }}</flux:heading>
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Base Salary') }}</flux:table.column>
                                <flux:table.column>{{ __('Allowances') }}</flux:table.column>
                                <flux:table.column>{{ __('Deductions') }}</flux:table.column>
                                <flux:table.column>{{ __('Net Salary') }}</flux:table.column>
                                <flux:table.column>{{ __('Effective From') }}</flux:table.column>
                                <flux:table.column>{{ __('Status') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($user->salaries as $salary)
                                    <flux:table.row>
                                        <flux:table.cell class="text-sm">{{ $salary->currency }} {{ number_format($salary->base_salary, 0) }}</flux:table.cell>
                                        <flux:table.cell class="text-sm text-zinc-500">{{ $salary->currency }} {{ number_format($salary->allowances, 0) }}</flux:table.cell>
                                        <flux:table.cell class="text-sm text-zinc-500">{{ $salary->currency }} {{ number_format($salary->deductions, 0) }}</flux:table.cell>
                                        <flux:table.cell class="text-sm font-medium">{{ $salary->currency }} {{ number_format($salary->net_salary, 0) }}</flux:table.cell>
                                        <flux:table.cell class="text-sm text-zinc-500">{{ $salary->effective_from->format('M d, Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            @if ($salary->status === 'active')
                                                <span class="hrms-badge-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="hrms-badge-neutral">{{ ucfirst($salary->status) }}</span>
                                            @endif
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
