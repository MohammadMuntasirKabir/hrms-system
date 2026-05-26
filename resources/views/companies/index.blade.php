<x-layouts::app :title="__('Companies')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Company Management') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('All companies and subsidiaries in the platform') }}</flux:subheading>
            </div>
            @can('companies.create')
                <flux:button :href="route('companies.create')" variant="primary" icon="plus">{{ __('Add Company') }}</flux:button>
            @endcan
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
                        <flux:table.column>{{ __('Company') }}</flux:table.column>
                        <flux:table.column class="hidden sm:table-cell">{{ __('Domain') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Depts') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Employees') }}</flux:table.column>
                        <flux:table.column class="hidden sm:table-cell text-center">{{ __('Contracts') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($companies as $company)
                            <flux:table.row>
                                <flux:table.cell>
                                    <a href="{{ route('companies.show', $company) }}" class="font-medium text-hrms-600 dark:text-hrms-400 hover:underline" wire:navigate>{{ $company->name }}</a>
                                    @if ($company->childCompanies->count() > 0)
                                        <span class="hrms-badge-neutral ml-1">{{ $company->childCompanies->count() }} {{ __('sub') }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="hidden sm:table-cell text-zinc-500 dark:text-zinc-400">{{ $company->domain ?? '—' }}</flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-info">{{ $company->departments_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-neutral">{{ $company->users_count }}</span></flux:table.cell>
                                <flux:table.cell class="hidden sm:table-cell text-center"><span class="hrms-badge-success">{{ $company->contracts_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center">
                                    @if ($company->is_active)
                                        <span class="hrms-badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="hrms-badge-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="hrms-actions justify-end">
                                        <flux:button :href="route('companies.show', $company)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                        @can('companies.edit')
                                            <flux:button :href="route('companies.edit', $company)" size="xs" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon"><flux:icon name="building-office" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No companies found.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">{{ $companies->links() }}</div>
    </div>
</x-layouts::app>
