<x-layouts::app :title="__('Departments')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Departments') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Manage organizational departments per company') }}</flux:subheading>
            </div>
            <flux:button :href="route('departments.create')" variant="primary" icon="plus">{{ __('Add Department') }}</flux:button>
        </div>

        <!-- Company Filter (Super Admin) -->
        @if($currentUser->isSuperAdmin() && $companies->count() > 1)
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('departments.index')" size="sm" variant="{{ !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All Companies') }}</flux:button>
                @foreach ($companies as $company)
                    <flux:button :href="route('departments.index', ['company_id' => $company->id])" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            </div>
        @endif

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
                        <flux:table.column>{{ __('Department') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column>{{ __('Head') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Employees') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Designations') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Contracts') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($departments as $dept)
                            <flux:table.row>
                                <flux:table.cell>
                                    <a href="{{ route('departments.show', $dept) }}" class="font-medium text-hrms-600 dark:text-hrms-400 hover:underline" wire:navigate>
                                        {{ $dept->name }}
                                    </a>
                                    @if ($dept->code)
                                        <span class="text-xs text-zinc-400 ml-1">({{ $dept->code }})</span>
                                    @endif
                                    @if ($dept->child_departments_count > 0)
                                        <span class="hrms-badge-neutral ml-1">{{ $dept->child_departments_count }} {{ __('sub') }}</span>
                                    @endif
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $dept->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell>
                                    @if ($dept->headUser)
                                        <div class="flex items-center gap-2">
                                            <flux:avatar :name="$dept->headUser->name" size="xs" />
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $dept->headUser->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-info">{{ $dept->users_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-neutral">{{ $dept->designations_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center"><span class="hrms-badge-neutral">{{ $dept->contracts_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center">
                                    @if ($dept->is_active)
                                        <span class="hrms-badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="hrms-badge-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2 justify-end">
                                        <flux:button :href="route('departments.show', $dept)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                        @can('departments.edit')
                                            <flux:button :href="route('departments.edit', $dept)" size="xs" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="8">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon">
                                            <flux:icon name="building-office-2" class="size-7 text-zinc-400 dark:text-zinc-600" />
                                        </div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No departments yet.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">
            {{ $departments->links() }}
        </div>
    </div>
</x-layouts::app>
