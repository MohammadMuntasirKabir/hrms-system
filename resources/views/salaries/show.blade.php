<x-layouts::app :title="__('Salary Details')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('salaries.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div class="flex-1">
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $salary->user->name }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Salary · :currency :amount', ['currency' => $salary->currency, 'amount' => number_format($salary->net_salary, 0)]) }} · {{ ucwords(str_replace('_', ' ', $salary->pay_frequency)) }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                @can('payroll.manage')
                    <flux:button :href="route('salaries.edit', $salary)" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                    <form method="POST" action="{{ route('salaries.destroy', $salary) }}" class="inline">
                        @csrf @method('DELETE')
                        <flux:button type="submit" variant="danger" icon="trash" onclick="return confirm('{{ __('Delete this salary record?') }}')">{{ __('Delete') }}</flux:button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="lg" class="mb-5 text-zinc-900 dark:text-white">{{ __('Salary Breakdown') }}</flux:heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Employee') }}</flux:text>
                            <div class="flex items-center gap-3 mt-1.5">
                                <flux:avatar :name="$salary->user->name" size="sm" />
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $salary->user->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $salary->user->email }}</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Company') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $salary->company?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Department') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $salary->department?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Designation') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $salary->designation?->title ?? '—' }}</p>
                        </div>
                    </div>

                    <flux:separator class="my-5" />

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                            <flux:text class="text-xs text-blue-600 dark:text-blue-400">{{ __('Base Salary') }}</flux:text>
                            <p class="text-lg font-bold text-blue-700 dark:text-blue-300 mt-1">{{ $salary->currency }} {{ number_format($salary->base_salary, 0) }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 p-4 text-center">
                            <flux:text class="text-xs text-emerald-600 dark:text-emerald-400">{{ __('Allowances') }}</flux:text>
                            <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300 mt-1">+{{ $salary->currency }} {{ number_format($salary->allowances, 0) }}</p>
                        </div>
                        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 text-center">
                            <flux:text class="text-xs text-red-600 dark:text-red-400">{{ __('Deductions') }}</flux:text>
                            <p class="text-lg font-bold text-red-700 dark:text-red-300 mt-1">-{{ $salary->currency }} {{ number_format($salary->deductions, 0) }}</p>
                        </div>
                        <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 p-4 text-center">
                            <flux:text class="text-xs text-purple-600 dark:text-purple-400">{{ __('Net Salary') }}</flux:text>
                            <p class="text-lg font-bold text-purple-700 dark:text-purple-300 mt-1">{{ $salary->currency }} {{ number_format($salary->net_salary, 0) }}</p>
                        </div>
                    </div>

                    <flux:separator class="my-5" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Pay Frequency') }}</flux:text>
                            <p class="mt-1.5"><span class="hrms-badge-info">{{ ucwords(str_replace('_', ' ', $salary->pay_frequency)) }}</span></p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Annual Salary') }}</flux:text>
                            <p class="mt-1.5 text-lg font-bold text-zinc-900 dark:text-white">{{ $salary->currency }} {{ number_format($salary->getAnnualSalary(), 0) }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Effective From') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $salary->effective_from->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Effective Until') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $salary->effective_until ? $salary->effective_until->format('F d, Y') : __('Ongoing') }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Status') }}</flux:text>
                            <p class="mt-1.5">
                                @if ($salary->status === 'active')<span class="hrms-badge-success">{{ __('Active') }}</span>
                                @elseif ($salary->status === 'revised')<span class="hrms-badge-warning">{{ __('Revised') }}</span>
                                @else<span class="hrms-badge-danger">{{ ucfirst($salary->status) }}</span>@endif
                            </p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Created By') }}</flux:text>
                            <p class="mt-1.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $salary->creator?->name ?? '—' }}</p>
                        </div>
                    </div>

                    @if ($salary->notes)
                        <flux:separator class="my-5" />
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Notes') }}</flux:text>
                            <p class="mt-1.5 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed whitespace-pre-wrap">{{ $salary->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Employee ID') }}</flux:text>
                        <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $salary->user->employee_id ?? '—' }}</p>
                    </div>
                </div>
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Contract') }}</flux:text>
                        @if ($salary->contract)
                            <p class="mt-1.5 text-sm font-medium text-zinc-900 dark:text-white">{{ $salary->contract->position }}</p>
                            <p class="text-xs text-zinc-500">{{ ucwords(str_replace('_', ' ', $salary->contract->contract_type)) }}</p>
                        @else
                            <p class="mt-1.5 text-sm text-zinc-400">—</p>
                        @endif
                    </div>
                </div>
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Record') }}</flux:text>
                        <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Created: :date', ['date' => $salary->created_at->format('M d, Y')]) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Updated: :date', ['date' => $salary->updated_at->format('M d, Y')]) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
