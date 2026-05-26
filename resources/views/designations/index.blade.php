<x-layouts::app :title="__('Designations')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Designations') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Manage job titles and hierarchy levels') }}</flux:subheading>
            </div>
            <flux:button :href="route('designations.create')" variant="primary" icon="plus">{{ __('Add Designation') }}</flux:button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('designations.index')" size="sm" variant="{{ !$filterDepartment ? 'primary' : 'outline' }}" wire:navigate>{{ __('All Departments') }}</flux:button>
            @foreach ($departments as $dept)
                <flux:button :href="route('designations.index', ['department_id' => $dept->id])" size="sm" variant="{{ $filterDepartment == $dept->id ? 'primary' : 'outline' }}" wire:navigate>{{ $dept->name }}</flux:button>
            @endforeach
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
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Department') }}</flux:table.column>
                        <flux:table.column>{{ __('Level') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column class="text-center">{{ __('Employees') }}</flux:table.column>
                        <flux:table.column class="text-center">{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($designations as $designation)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $designation->title }}</div>
                                        @if ($designation->description)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-1">{{ $designation->description }}</div>
                                        @endif
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($designation->department)
                                        <a href="{{ route('departments.show', $designation->department) }}" class="hrms-badge-info hover:underline" wire:navigate>{{ $designation->department->name }}</a>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $levelLabels = ['Entry', 'Mid', 'Senior', 'Lead', 'Manager', 'Director', 'VP', 'SVP', 'EVP', 'C-Level', 'Owner'];
                                        $levelLabel = $levelLabels[$designation->level] ?? 'Level ' . $designation->level;
                                        $levelColors = ['neutral', 'neutral', 'info', 'info', 'warning', 'warning', 'purple', 'purple', 'purple', 'danger', 'danger'];
                                        $levelColor = $levelColors[$designation->level] ?? 'neutral';
                                    @endphp
                                    <span class="hrms-badge-{{ $levelColor }}">{{ $levelLabel }}</span>
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $designation->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell class="text-center"><span class="hrms-badge-neutral">{{ $designation->users_count }}</span></flux:table.cell>
                                <flux:table.cell class="text-center">
                                    @if ($designation->is_active)
                                        <span class="hrms-badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="hrms-badge-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="hrms-actions justify-end">
                                        <flux:button :href="route('designations.edit', $designation)" size="xs" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                                        <form method="POST" action="{{ route('designations.destroy', $designation) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <flux:button type="submit" size="xs" variant="danger" icon="trash" onclick="return confirm('{{ __('Delete this designation?') }}')">{{ __('Delete') }}</flux:button>
                                        </form>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon"><flux:icon name="briefcase" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No designations yet.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">{{ $designations->links() }}</div>
    </div>
</x-layouts::app>
