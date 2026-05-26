<x-layouts::app :title="__('Companies')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Company Management') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('All companies and subsidiaries in the platform') }}</flux:subheading>
            </div>
        </div>

        <div class="hrms-card flex-1 min-h-0">
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Company') }}</flux:table.column>
                        <flux:table.column>{{ __('Domain') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Depts') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Employees') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Designations') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Contracts') }}</flux:table.column>
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
                                <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $company->domain ?? '—' }}</flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-info">{{ $company->departments_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-neutral">{{ $company->users_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-purple">{{ $company->designations_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-success">{{ $company->contracts_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center">
                                    @if ($company->is_active)<span class="hrms-badge-success">{{ __('Active') }}</span>
                                    @else<span class="hrms-badge-danger">{{ __('Inactive') }}</span>@endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="hrms-actions justify-end">
                                        <flux:button :href="route('companies.show', $company)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="8">
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
