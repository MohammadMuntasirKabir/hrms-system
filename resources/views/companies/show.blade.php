<x-layouts::app :title="$company->name">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('companies.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $company->name }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ $company->domain ?? 'No domain' }} · {{ $company->country }} · {{ $company->timezone }}</flux:subheading>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="users" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Employees') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $company->users_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="building-office-2" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Departments') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $company->departments_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-amber-50 dark:bg-amber-900/30"><flux:icon name="briefcase" class="size-5 text-amber-600 dark:text-amber-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Designations') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $company->designations_count }}</p>
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
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $company->contracts_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 flex-1 min-h-0">
            <!-- Departments -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="md" class="text-zinc-900 dark:text-white">{{ __('Departments') }}</flux:heading>
                        <flux:button :href="route('departments.index')" size="xs" variant="outline" icon="arrow-right" wire:navigate>{{ __('View All') }}</flux:button>
                    </div>
                    @forelse ($departments as $dept)
                        <div class="hrms-list-row flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                            <a href="{{ route('departments.show', $dept) }}" class="font-medium text-hrms-600 dark:text-hrms-400 hover:underline text-sm" wire:navigate>{{ $dept->name }}</a>
                            <div class="hrms-actions">
                                <span class="hrms-badge-info text-xs px-2 py-0.5">{{ $dept->users_count }} {{ __('emp') }}</span>
                                <span class="hrms-badge-neutral text-xs px-2 py-0.5">{{ $dept->designations_count }} {{ __('desig') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-zinc-400 text-sm">{{ __('No departments yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Designations -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="md" class="text-zinc-900 dark:text-white">{{ __('Designations') }}</flux:heading>
                        <flux:button :href="route('designations.index')" size="xs" variant="outline" icon="arrow-right" wire:navigate>{{ __('View All') }}</flux:button>
                    </div>
                    @forelse ($designations as $desig)
                        <div class="hrms-list-row flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                            <div>
                                <span class="font-medium text-sm text-zinc-900 dark:text-white">{{ $desig->title }}</span>
                                @if ($desig->department)
                                    <span class="text-xs text-zinc-400 ml-1">· {{ $desig->department->name }}</span>
                                @endif
                            </div>
                            <span class="hrms-badge-neutral text-xs px-2 py-0.5">{{ $desig->users_count }} {{ __('emp') }}</span>
                        </div>
                    @empty
                        <div class="text-center py-6 text-zinc-400 text-sm">{{ __('No designations yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Contracts -->
        @if ($contracts->count() > 0)
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="md" class="text-zinc-900 dark:text-white">{{ __('Recent Contracts') }}</flux:heading>
                        <flux:button :href="route('contracts.index')" size="xs" variant="outline" icon="arrow-right" wire:navigate>{{ __('View All') }}</flux:button>
                    </div>
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Employee') }}</flux:table.column>
                                <flux:table.column>{{ __('Department') }}</flux:table.column>
                                <flux:table.column>{{ __('Position') }}</flux:table.column>
                                <flux:table.column>{{ __('Status') }}</flux:table.column>
                                <flux:table.column>{{ __('Actions') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($contracts as $c)
                                    <flux:table.row>
                                        <flux:table.cell>
                                            <div class="flex items-center gap-2">
                                                <flux:avatar :name="$c->user->name" size="xs" />
                                                <span class="text-sm font-medium">{{ $c->user->name }}</span>
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-sm text-zinc-500">{{ $c->department?->name ?? '—' }}</flux:table.cell>
                                        <flux:table.cell class="text-sm text-zinc-500">{{ $c->position }}</flux:table.cell>
                                        <flux:table.cell>
                                            @if ($c->status === 'active')<span class="hrms-badge-success text-xs">{{ __('Active') }}</span>
                                            @elseif ($c->is_expired)<span class="hrms-badge-danger text-xs">{{ __('Expired') }}</span>
                                            @else<span class="hrms-badge-neutral text-xs">{{ ucfirst($c->status) }}</span>@endif
                                        </flux:table.cell>
                                        <flux:table.cell>
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
    </div>
</x-layouts::app>
