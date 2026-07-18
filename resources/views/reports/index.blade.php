<x-layouts::app :title="__('Reports')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Reports & Analytics') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Workforce insights across the organization') }}</flux:subheading>
            </div>
            @can('reports.export')
                <flux:button :href="route('reports.export', request()->only('company_id'))" variant="outline" icon="arrow-down-tray">{{ __('Export CSV') }}</flux:button>
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
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
