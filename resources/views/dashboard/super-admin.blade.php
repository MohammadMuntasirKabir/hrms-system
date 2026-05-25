<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Super Admin Dashboard') }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">
                @if(request('company_id') || session('filter_company_id'))
                    {{ __('Filtered by company') }}
                @else
                    {{ __('Platform overview across all companies') }}
                @endif
            </flux:subheading>
        </div>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30">
                            <flux:icon name="building-office" class="size-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Companies') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $stats['totalCompanies'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30">
                            <flux:icon name="users" class="size-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Users') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $stats['totalEmployees'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30">
                            <flux:icon name="briefcase" class="size-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Designations') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $stats['totalDesignations'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-amber-50 dark:bg-amber-900/30">
                            <flux:icon name="document-text" class="size-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Active Contracts') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $stats['activeContracts'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($recentItems['recentCompanies']->count() > 0)
            <div class="hrms-card flex-1 min-h-0">
                <div class="hrms-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">{{ __('Recent Companies') }}</flux:heading>
                        <flux:button :href="route('companies.index')" variant="outline" size="sm" wire:navigate icon="arrow-right">{{ __('View All') }}</flux:button>
                    </div>
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Company') }}</flux:table.column>
                                <flux:table.column>{{ __('Domain') }}</flux:table.column>
                                <flux:table.column>{{ __('Users') }}</flux:table.column>
                                <flux:table.column>{{ __('Status') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse ($recentItems['recentCompanies'] as $company)
                                    <flux:table.row>
                                        <flux:table.cell>
                                            <a href="{{ route('companies.show', $company) }}" class="font-medium text-hrms-600 dark:text-hrms-400 hover:underline" wire:navigate>{{ $company->name }}</a>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $company->domain ?? '—' }}</flux:table.cell>
                                        <flux:table.cell><span class="hrms-badge-neutral">{{ $company->users_count }}</span></flux:table.cell>
                                        <flux:table.cell>
                                            @if ($company->is_active)<span class="hrms-badge-success">{{ __('Active') }}</span>
                                            @else<span class="hrms-badge-danger">{{ __('Inactive') }}</span>@endif
                                        </flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row><flux:table.cell colspan="4"><div class="hrms-empty-state py-8"><div class="hrms-empty-state-icon"><flux:icon name="building-office" class="size-7 text-zinc-400 dark:text-zinc-600" /></div><flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No companies yet.') }}</flux:text></div></flux:table.cell></flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
