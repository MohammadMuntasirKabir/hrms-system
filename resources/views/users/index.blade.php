<x-layouts::app :title="__('Employees')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Employee Management') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Manage employees across your organization') }}</flux:subheading>
            </div>
            @if (auth()->user()->can('users.create'))
                <flux:button :href="route('users.create')" variant="primary" icon="plus">{{ __('Add Employee') }}</flux:button>
            @endif
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('users.index')" size="sm" variant="{{ !$filterDepartment && !$filterStatus && !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All') }}</flux:button>

            @if($currentUser->isSuperAdmin() && $companies->count() > 1)
                @foreach ($companies as $company)
                    <flux:button :href="route('users.index', array_merge(request()->query(), ['company_id' => $company->id]))" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            @endif

            @if($departments->count() > 0)
                <span class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1 self-center"></span>
                @foreach ($departments as $dept)
                    <flux:button :href="route('users.index', array_merge(request()->query(), ['department_id' => $dept->id]))" size="sm" variant="{{ $filterDepartment == $dept->id ? 'primary' : 'outline' }}" wire:navigate>{{ $dept->name }}</flux:button>
                @endforeach
            @endif

            <span class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1 self-center"></span>
            <flux:button :href="route('users.index', array_merge(request()->query(), ['status' => 'active']))" size="sm" variant="{{ $filterStatus === 'active' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Active') }}</flux:button>
            <flux:button :href="route('users.index', array_merge(request()->query(), ['status' => 'inactive']))" size="sm" variant="{{ $filterStatus === 'inactive' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Inactive') }}</flux:button>
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
                        <flux:table.column>{{ __('Employee') }}</flux:table.column>
                        <flux:table.column>{{ __('Department') }}</flux:table.column>
                        <flux:table.column>{{ __('Designation') }}</flux:table.column>
                        <flux:table.column>{{ __('Role') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column>{{ __('Contracts') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($users as $user)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar :name="$user->name" size="sm" />
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($user->department)
                                        <a href="{{ route('departments.show', $user->department) }}" class="hrms-badge-info hover:underline" wire:navigate>{{ $user->department->name }}</a>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($user->designation)
                                        <span class="hrms-badge-purple">{{ $user->designation->title }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <span class="hrms-badge-neutral">{{ ucwords(str_replace('_', ' ', $user->getRoleNames()->first() ?? '—')) }}</span>
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $user->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell>
                                    @if ($user->contracts_count > 0)
                                        <a href="{{ route('contracts.index', ['department_id' => $user->department_id]) }}" class="hrms-badge-success hover:underline" wire:navigate>{{ $user->contracts_count }}</a>
                                    @else
                                        <span class="text-zinc-400">0</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($user->is_active)
                                        <span class="hrms-badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="hrms-badge-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2 justify-end">
                                        @if ($currentUser->can('users.edit'))
                                            <flux:button :href="route('users.edit', $user)" size="xs" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                                        @endif
                                        @if ($currentUser->can('users.delete') && $user->id !== $currentUser->id)
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                                                @csrf @method('DELETE')
                                                <flux:button type="submit" size="xs" variant="danger" icon="trash" onclick="return confirm('{{ __('Delete this employee and all their contracts?') }}')">{{ __('Delete') }}</flux:button>
                                            </form>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="8">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon"><flux:icon name="users" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No employees found.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">{{ $users->links() }}</div>
    </div>
</x-layouts::app>
