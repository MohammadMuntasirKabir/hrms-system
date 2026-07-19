<x-layouts::app :title="__('Reports')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Reports & Analytics') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Workforce insights across the organization') }}</flux:subheading>
            </div>
            @can('reports.export')
                <div class="flex flex-wrap gap-2">
                    <flux:button :href="route('reports.export', request()->only('company_id'))" variant="outline" icon="arrow-down-tray">{{ __('Export Employees CSV') }}</flux:button>
                    <flux:button :href="route('reports.export-expiring', request()->only('company_id'))" variant="outline" icon="arrow-down-tray">{{ __('Export Expiring Contracts') }}</flux:button>
                </div>
            @endcan
        </div>

        @if (auth()->user()->isSuperAdmin() && $companies->count() > 1)
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('reports.index')" size="sm" variant="{{ !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All Companies') }}</flux:button>
                @foreach ($companies as $company)
                    <flux:button :href="route('reports.index', ['company_id' => $company->id])" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            </div>
        @endif

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="users" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Employees') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $totalEmployees }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30"><flux:icon name="building-office" class="size-5 text-emerald-600 dark:text-emerald-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Companies') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $totalCompanies }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="document-text" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Active Contracts') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $activeContracts }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-amber-50 dark:bg-amber-900/30"><flux:icon name="currency-dollar" class="size-5 text-amber-600 dark:text-amber-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Monthly Payroll') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ number_format($totalPayroll, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid auto-rows-min gap-4 lg:grid-cols-2">
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">{{ __('Headcount by Department') }}</flux:heading>
                    @if ($byDepartment->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach ($byDepartment as $dept)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $dept->name }}</span>
                                    <span class="hrms-badge-info">{{ $dept->users_count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="hrms-empty-state py-8">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No department data available.') }}</flux:text>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white mb-4">{{ __('Leave Summary') }}</flux:heading>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Pending') }}</span>
                            <span class="hrms-badge-warning">{{ $leaveStats['pending'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Approved') }}</span>
                            <span class="hrms-badge-success">{{ $leaveStats['approved'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Rejected') }}</span>
                            <span class="hrms-badge-danger">{{ $leaveStats['rejected'] }}</span>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-zinc-100 dark:border-zinc-800 pt-4">
                        <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-3">{{ __('Leave Utilization') }}</flux:text>
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Approved Leave Days') }}</span>
                                <span class="hrms-badge-info">{{ $leaveUtilization['approved_days'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Avg Days / Approval') }}</span>
                                <span class="hrms-badge-neutral">{{ $leaveUtilization['avg_days_per_approved'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($expiringContracts->count() > 0)
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">{{ __('Contracts Expiring (Next 30 Days)') }}</flux:heading>
                        @can('reports.export')
                            <flux:button :href="route('reports.export-expiring', request()->only('company_id'))" variant="outline" size="sm" icon="arrow-down-tray">{{ __('Export') }}</flux:button>
                        @endcan
                    </div>
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Employee') }}</flux:table.column>
                                @if(auth()->user()->isSuperAdmin())
                                    <flux:table.column>{{ __('Company') }}</flux:table.column>
                                @endif
                                <flux:table.column>{{ __('Position') }}</flux:table.column>
                                <flux:table.column>{{ __('Ends') }}</flux:table.column>
                                <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($expiringContracts as $contract)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ $contract->user?->name ?? '—' }}</flux:table.cell>
                                        @if(auth()->user()->isSuperAdmin())
                                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $contract->company?->name ?? '—' }}</flux:table.cell>
                                        @endif
                                        <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $contract->position }}</flux:table.cell>
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
