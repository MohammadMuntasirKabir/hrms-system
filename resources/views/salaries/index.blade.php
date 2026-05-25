<x-layouts::app :title="__('Salaries')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Salary Management') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Manage employee compensation and payroll') }}</flux:subheading>
            </div>
            @can('payroll.manage')
                <flux:button :href="route('salaries.create')" variant="primary" icon="plus">{{ __('Add Salary') }}</flux:button>
            @endcan
        </div>

        <!-- Stats -->
        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30"><flux:icon name="currency-dollar" class="size-5 text-emerald-600 dark:text-emerald-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Monthly Payroll') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">BDT {{ number_format($totalPayroll, 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="chart-bar" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Average Salary') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">BDT {{ number_format($avgSalary, 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="users" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Active Salaries') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $salaries->total() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('salaries.index')" size="sm" variant="{{ !$filterStatus && !$filterDepartment && !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All') }}</flux:button>
            <flux:button :href="route('salaries.index', array_merge(request()->query(), ['status' => 'active']))" size="sm" variant="{{ $filterStatus === 'active' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Active') }}</flux:button>
            <flux:button :href="route('salaries.index', array_merge(request()->query(), ['status' => 'inactive']))" size="sm" variant="{{ $filterStatus === 'inactive' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Inactive') }}</flux:button>
            <flux:button :href="route('salaries.index', array_merge(request()->query(), ['status' => 'revised']))" size="sm" variant="{{ $filterStatus === 'revised' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Revised') }}</flux:button>
            @foreach ($departments as $dept)
                <flux:button :href="route('salaries.index', array_merge(request()->query(), ['department_id' => $dept->id]))" size="sm" variant="{{ $filterDepartment == $dept->id ? 'primary' : 'outline' }}" wire:navigate>{{ $dept->name }}</flux:button>
            @endforeach
            @if($currentUser->isSuperAdmin() && $companies->count() > 1)
                @foreach ($companies as $company)
                    <flux:button :href="route('salaries.index', array_merge(request()->query(), ['company_id' => $company->id]))" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            @endif
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 p-4 flex items-center gap-3">
                <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</span>
            </div>
        @endif

        <div class="hrms-card flex-1 min-h-0">
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Employee') }}</flux:table.column>
                        <flux:table.column>{{ __('Department') }}</flux:table.column>
                        <flux:table.column>{{ __('Designation') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column class="text-right">{{ __('Base Salary') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Net Salary') }}</flux:table.column>
                        <flux:table.column>{{ __('Frequency') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($salaries as $salary)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar :name="$salary->user->name" size="sm" />
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $salary->user->name }}</span>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($salary->department)
                                        <a href="{{ route('departments.show', $salary->department) }}" class="hrms-badge-info hover:underline" wire:navigate>{{ $salary->department->name }}</a>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($salary->designation)
                                        <span class="hrms-badge-purple">{{ $salary->designation->title }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $salary->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell class="text-right font-mono text-sm">{{ $salary->currency }} {{ number_format($salary->base_salary, 0) }}</flux:table.cell>
                                <flux:table.cell class="text-right font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $salary->currency }} {{ number_format($salary->net_salary, 0) }}</flux:table.cell>
                                <flux:table.cell><span class="hrms-badge-neutral">{{ ucwords(str_replace('_', ' ', $salary->pay_frequency)) }}</span></flux:table.cell>
                                <flux:table.cell>
                                    @if ($salary->status === 'active')
                                        <span class="hrms-badge-success">{{ __('Active') }}</span>
                                    @elseif ($salary->status === 'revised')
                                        <span class="hrms-badge-warning">{{ __('Revised') }}</span>
                                    @else
                                        <span class="hrms-badge-danger">{{ ucfirst($salary->status) }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2 justify-end">
                                        <flux:button :href="route('salaries.show', $salary)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                        @can('payroll.manage')
                                            <flux:button :href="route('salaries.edit', $salary)" size="xs" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="9">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon"><flux:icon name="currency-dollar" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No salary records found.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">{{ $salaries->links() }}</div>
    </div>
</x-layouts::app>
