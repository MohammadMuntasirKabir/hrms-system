<x-layouts::app :title="__('Contracts')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Contracts') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Manage employee contracts and agreements') }}</flux:subheading>
            </div>
            <flux:button :href="route('contracts.create')" variant="primary" icon="plus">{{ __('New Contract') }}</flux:button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('contracts.index')" size="sm" variant="{{ !$filterStatus && !$filterDepartment && !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All') }}</flux:button>
            <flux:button :href="route('contracts.index', array_merge(request()->query(), ['status' => 'active']))" size="sm" variant="{{ $filterStatus === 'active' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Active') }}</flux:button>
            <flux:button :href="route('contracts.index', array_merge(request()->query(), ['status' => 'expired']))" size="sm" variant="{{ $filterStatus === 'expired' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Expired') }}</flux:button>
            <flux:button :href="route('contracts.index', array_merge(request()->query(), ['status' => 'terminated']))" size="sm" variant="{{ $filterStatus === 'terminated' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Terminated') }}</flux:button>
            <flux:button :href="route('contracts.index', array_merge(request()->query(), ['status' => 'draft']))" size="sm" variant="{{ $filterStatus === 'draft' ? 'primary' : 'outline' }}" wire:navigate>{{ __('Draft') }}</flux:button>

            @if($currentUser->isSuperAdmin() && $companies->count() > 1)
                <span class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1 self-center"></span>
                @foreach ($companies as $company)
                    <flux:button :href="route('contracts.index', array_merge(request()->query(), ['company_id' => $company->id]))" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            @endif

            @if($departments->count() > 0)
                <span class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1 self-center"></span>
                @foreach ($departments as $dept)
                    <flux:button :href="route('contracts.index', array_merge(request()->query(), ['department_id' => $dept->id]))" size="sm" variant="{{ $filterDepartment == $dept->id ? 'primary' : 'outline' }}" wire:navigate>{{ $dept->name }}</flux:button>
                @endforeach
            @endif
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
                        <flux:table.column>{{ __('Position') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Duration') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($contracts as $contract)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar :name="$contract->user->name" size="sm" />
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-white">{{ $contract->user->name }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $contract->user->designation?->title ?? '—' }}</div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($contract->department)
                                        <a href="{{ route('departments.show', $contract->department) }}" class="hrms-badge-info hover:underline" wire:navigate>{{ $contract->department->name }}</a>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-zinc-700 dark:text-zinc-300">{{ $contract->position }}</flux:table.cell>
                                <flux:table.cell><span class="hrms-badge-purple">{{ ucwords(str_replace('_', ' ', $contract->contract_type)) }}</span></flux:table.cell>
                                <flux:table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $contract->start_date->format('M d, Y') }}
                                    @if ($contract->end_date)
                                        <span class="text-zinc-400">→</span> {{ $contract->end_date->format('M d, Y') }}
                                    @else
                                        <span class="text-zinc-400">→</span> <span class="text-emerald-600 dark:text-emerald-400">{{ __('Ongoing') }}</span>
                                    @endif
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $contract->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell>
                                    @if ($contract->status === 'active')
                                        @if ($contract->is_expiring_soon)
                                            <span class="hrms-badge-warning">{{ __('Expiring Soon') }}</span>
                                        @else
                                            <span class="hrms-badge-success">{{ __('Active') }}</span>
                                        @endif
                                    @elseif ($contract->status === 'expired' || $contract->is_expired)
                                        <span class="hrms-badge-danger">{{ __('Expired') }}</span>
                                    @elseif ($contract->status === 'terminated')
                                        <span class="hrms-badge-danger">{{ __('Terminated') }}</span>
                                    @else
                                        <span class="hrms-badge-neutral">{{ ucfirst($contract->status) }}</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2 justify-end">
                                        <flux:button :href="route('contracts.show', $contract)" size="xs" variant="outline" icon="eye" wire:navigate>{{ __('View') }}</flux:button>
                                        @can('contracts.edit')
                                            <flux:button :href="route('contracts.edit', $contract)" size="xs" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="8">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon"><flux:icon name="document-text" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No contracts found.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">{{ $contracts->links() }}</div>
    </div>
</x-layouts::app>
