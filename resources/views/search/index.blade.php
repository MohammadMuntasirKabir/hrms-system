<x-layouts::app :title="__('Search')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Global Search') }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Find employees, departments, companies, contracts and applicants across your organization.') }}</flux:subheading>
        </div>

        <form method="GET" action="{{ route('search') }}" class="hrms-card">
            <div class="hrms-card-body">
                <flux:input name="q" type="search" value="{{ $term }}" placeholder="{{ __('Search by name, email, employee ID, department, position…') }}" icon="magnifying-glass" autofocus>
                    <flux:button type="submit" variant="primary" icon="magnifying-glass" class="ms-2">{{ __('Search') }}</flux:button>
                </flux:input>
            </div>
        </form>

        @if (strlen($term) < 2)
            <div class="hrms-card">
                <div class="hrms-empty-state py-12">
                    <div class="hrms-empty-state-icon"><flux:icon name="magnifying-glass" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                    <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Type at least 2 characters to search.') }}</flux:text>
                </div>
            </div>
        @elseif ($total === 0)
            <div class="hrms-card">
                <div class="hrms-empty-state py-12">
                    <div class="hrms-empty-state-icon"><flux:icon name="magnifying-glass" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                    <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No results found for ":term".', ['term' => $term]) }}</flux:text>
                </div>
            </div>
        @else
            <flux:heading size="md" class="text-zinc-500 dark:text-zinc-400">{{ __(':total result(s) for ":term"', ['total' => $total, 'term' => $term]) }}</flux:heading>

            @if ($results['users']->count() > 0)
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Employees') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                                    @if(auth()->user()->isSuperAdmin())
                                        <flux:table.column>{{ __('Company') }}</flux:table.column>
                                    @endif
                                    <flux:table.column>{{ __('Department') }}</flux:table.column>
                                    <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($results['users'] as $u)
                                        <flux:table.row>
                                            <flux:table.cell>
                                                <div class="flex items-center gap-2">
                                                    <flux:avatar :name="$u->name" size="xs" />
                                                    <div>
                                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $u->name }}</div>
                                                        <div class="text-xs text-zinc-400">{{ $u->employee_id ? '#'.$u->employee_id.' · ' : '' }}{{ $u->email }}</div>
                                                    </div>
                                                </div>
                                            </flux:table.cell>
                                            @if(auth()->user()->isSuperAdmin())
                                                <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $u->company?->name ?? '—' }}</flux:table.cell>
                                            @endif
                                            <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $u->department?->name ?? '—' }}</flux:table.cell>
                                            <flux:table.cell class="text-right">
                                                <div class="hrms-actions justify-end">
                                                    <flux:button :href="route('users.show', $u)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
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

            @if ($results['departments']->count() > 0)
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Departments') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                                    <flux:table.column>{{ __('Code') }}</flux:table.column>
                                    @if(auth()->user()->isSuperAdmin())
                                        <flux:table.column>{{ __('Company') }}</flux:table.column>
                                    @endif
                                    <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($results['departments'] as $d)
                                        <flux:table.row>
                                            <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ $d->name }}</flux:table.cell>
                                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $d->code ?? '—' }}</flux:table.cell>
                                            @if(auth()->user()->isSuperAdmin())
                                                <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $d->company?->name ?? '—' }}</flux:table.cell>
                                            @endif
                                            <flux:table.cell class="text-right">
                                                <div class="hrms-actions justify-end">
                                                    <flux:button :href="route('departments.show', $d)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
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

            @if ($results['companies']->count() > 0)
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Companies') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                                    <flux:table.column>{{ __('Domain') }}</flux:table.column>
                                    <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($results['companies'] as $c)
                                        <flux:table.row>
                                            <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ $c->name }}</flux:table.cell>
                                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $c->domain ?? '—' }}</flux:table.cell>
                                            <flux:table.cell class="text-right">
                                                <div class="hrms-actions justify-end">
                                                    <flux:button :href="route('companies.show', $c)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
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

            @if ($results['contracts']->count() > 0)
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Contracts') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Position') }}</flux:table.column>
                                    <flux:table.column>{{ __('Employee') }}</flux:table.column>
                                    <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($results['contracts'] as $c)
                                        <flux:table.row>
                                            <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ $c->position }}</flux:table.cell>
                                            <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $c->user?->name ?? '—' }}</flux:table.cell>
                                            <flux:table.cell class="text-right">
                                                <div class="hrms-actions justify-end">
                                                    <flux:button :href="route('contracts.show', $c)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
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

            @if ($results['applicants']->count() > 0)
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Applicants') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                                    <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($results['applicants'] as $a)
                                        <flux:table.row>
                                            <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ $a->full_name }}</flux:table.cell>
                                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $a->email }}</flux:table.cell>
                                            <flux:table.cell class="text-right">
                                                <div class="hrms-actions justify-end">
                                                    <flux:button :href="route('applicants.show', $a)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
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
        @endif
    </div>
</x-layouts::app>
