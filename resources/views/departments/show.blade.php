<x-layouts::app :title="$department->name">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('departments.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div class="flex-1">
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $department->name }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ $department->code ? $department->code . ' · ' : '' }}{{ $department->company?->name ?? '—' }}</flux:subheading>
            </div>
            <div class="hrms-actions">
                @can('departments.edit')
                    <flux:button :href="route('departments.edit', $department)" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                @endcan
                @can('departments.delete')
                    <form method="POST" action="{{ route('departments.destroy', $department) }}" class="inline">
                        @csrf @method('DELETE')
                        <flux:button type="submit" variant="danger" icon="trash" onclick="return confirm('{{ __('Delete this department?') }}')">{{ __('Delete') }}</flux:button>
                    </form>
                @endcan
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
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $department->users_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="briefcase" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Designations') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $department->designations_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-amber-50 dark:bg-amber-900/30"><flux:icon name="document-text" class="size-5 text-amber-600 dark:text-amber-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Contracts') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $department->contracts_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30"><flux:icon name="building-office-2" class="size-5 text-emerald-600 dark:text-emerald-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sub-Depts') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $department->childDepartments()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 flex-1 min-h-0">
            <!-- Employees -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Employees') }}</flux:heading>
                    @forelse ($employees as $emp)
                        <div class="hrms-list-row flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$emp->name" size="xs" />
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $emp->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $emp->designation?->title ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="hrms-actions">
                                @if ($emp->activeContract)
                                    <span class="hrms-badge-success text-xs px-2 py-0.5">{{ ucwords(str_replace('_', ' ', $emp->activeContract->contract_type)) }}</span>
                                @else
                                    <span class="hrms-badge-neutral text-xs px-2 py-0.5">{{ __('No contract') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-zinc-400 text-sm">{{ __('No employees in this department.') }}</div>
                    @endforelse
                    @if ($department->users_count > 10)
                        <div class="mt-3 text-center">
                            <a href="{{ route('users.index', ['department_id' => $department->id]) }}" class="text-sm text-hrms-600 dark:text-hrms-400 hover:underline" wire:navigate>{{ __('View all :count employees', ['count' => $department->users_count]) }}</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Designations -->
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Designations') }}</flux:heading>
                    @forelse ($designations as $desig)
                        <div class="hrms-list-row flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $desig->title }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">Level {{ $desig->level }}</div>
                            </div>
                            <span class="hrms-badge-info text-xs px-2 py-0.5">{{ $desig->users_count }} {{ __('employees') }}</span>
                        </div>
                    @empty
                        <div class="text-center py-6 text-zinc-400 text-sm">{{ __('No designations in this department.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Contracts -->
        @if ($contracts->count() > 0)
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Contracts') }}</flux:heading>
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Employee') }}</flux:table.column>
                                <flux:table.column>{{ __('Position') }}</flux:table.column>
                                <flux:table.column>{{ __('Type') }}</flux:table.column>
                                <flux:table.column>{{ __('Status') }}</flux:table.column>
                                <flux:table.column>{{ __('Actions') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($contracts as $contract)
                                    <flux:table.row>
                                        <flux:table.cell>
                                            <div class="flex items-center gap-2">
                                                <flux:avatar :name="$contract->user->name" size="xs" />
                                                <span class="text-sm font-medium">{{ $contract->user->name }}</span>
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-sm text-zinc-500">{{ $contract->position }}</flux:table.cell>
                                        <flux:table.cell><span class="hrms-badge-info text-xs">{{ ucwords(str_replace('_', ' ', $contract->contract_type)) }}</span></flux:table.cell>
                                        <flux:table.cell>
                                            @if ($contract->status === 'active')
                                                <span class="hrms-badge-success text-xs">{{ __('Active') }}</span>
                                            @elseif ($contract->is_expired)
                                                <span class="hrms-badge-danger text-xs">{{ __('Expired') }}</span>
                                            @else
                                                <span class="hrms-badge-neutral text-xs">{{ ucfirst($contract->status) }}</span>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
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

        <!-- Child Departments -->
        @if ($department->childDepartments->count() > 0)
            <div class="hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Sub-Departments') }}</flux:heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($department->childDepartments as $child)
                            <a href="{{ route('departments.show', $child) }}" class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors" wire:navigate>
                                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                    <flux:icon name="building-office-2" class="size-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <div class="font-medium text-sm text-zinc-900 dark:text-white">{{ $child->name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $child->users()->count() }} {{ __('employees') }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
