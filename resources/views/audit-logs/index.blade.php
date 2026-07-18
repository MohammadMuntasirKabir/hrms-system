<x-layouts::app :title="__('Audit Log')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Audit Log') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Track all system changes') }}</flux:subheading>
            </div>
        </div>

        @if($currentUser->isSuperAdmin() && $companies->count() > 1)
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('audit-logs.index')" size="sm" variant="{{ !$filterCompany ? 'primary' : 'outline' }}" wire:navigate>{{ __('All Companies') }}</flux:button>
                @foreach ($companies as $company)
                    <flux:button :href="route('audit-logs.index', ['company_id' => $company->id])" size="sm" variant="{{ $filterCompany == $company->id ? 'primary' : 'outline' }}" wire:navigate>{{ $company->name }}</flux:button>
                @endforeach
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('audit-logs.index')" size="sm" variant="outline" wire:navigate>{{ __('All') }}</flux:button>
            @foreach (['created', 'updated', 'deleted'] as $action)
                <flux:button :href="route('audit-logs.index', array_merge(request()->except('action') ?? [], ['action' => $action]))" size="sm" variant="{{ $actionFilter === $action ? 'primary' : 'outline' }}" wire:navigate>{{ ucfirst($action) }}</flux:button>
            @endforeach
        </div>

        <div class="hrms-card flex-1 min-h-0">
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                        <flux:table.column>{{ __('User') }}</flux:table.column>
                        @if ($currentUser->isSuperAdmin())
                            <flux:table.column>{{ __('Company') }}</flux:table.column>
                        @endif
                        <flux:table.column>{{ __('Action') }}</flux:table.column>
                        <flux:table.column>{{ __('Description') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($logs as $log)
                            <flux:table.row>
                                <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $log->created_at->format('M d, Y H:i') }}</flux:table.cell>
                                <flux:table.cell>
                                    @if ($log->user)
                                        <div class="flex items-center gap-2">
                                            <flux:avatar :name="$log->user->name" size="xs" />
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $log->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">System</span>
                                    @endif
                                </flux:table.cell>
                                @if ($currentUser->isSuperAdmin())
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $log->company?->name ?? '—' }}</flux:table.cell>
                                @endif
                                <flux:table.cell>
                                    <span class="hrms-badge-neutral">{{ ucfirst($log->action) }}</span>
                                </flux:table.cell>
                                <flux:table.cell class="text-zinc-700 dark:text-zinc-300">{{ $log->description }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5">
                                    <div class="hrms-empty-state py-12">
                                        <div class="hrms-empty-state-icon">
                                            <flux:icon name="list-bullet" class="size-7 text-zinc-400 dark:text-zinc-600" />
                                        </div>
                                        <flux:text class="text-zinc-500 dark:text-zinc-400 mb-1">{{ __('No audit entries yet.') }}</flux:text>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex justify-center">
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts::app>
