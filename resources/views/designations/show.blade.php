<x-layouts::app :title="$designation->title">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('designations.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div class="flex-1">
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $designation->title }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ $designation->company?->name ?? '—' }}</flux:subheading>
            </div>
            <div class="hrms-actions">
                <flux:button :href="route('designations.edit', $designation)" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                @if ($designation->users_count === 0)
                    <form method="POST" action="{{ route('designations.destroy', $designation) }}" class="inline">
                        @csrf @method('DELETE')
                        <flux:button type="submit" variant="danger" icon="trash" onclick="return confirm('{{ __('Delete this designation?') }}')">{{ __('Delete') }}</flux:button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="building-office" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Company') }}</flux:text>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $designation->company?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="squares-2x2" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Department') }}</flux:text>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $designation->department?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30"><flux:icon name="users" class="size-5 text-emerald-600 dark:text-emerald-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Employees') }}</flux:text>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white mt-0.5">{{ $designation->users_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="hrms-card">
            <div class="hrms-card-body">
                <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Details') }}</flux:heading>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Level') }}</flux:text>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white mt-1">{{ $designation->level }}</p>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Status') }}</flux:text>
                        <p class="text-sm font-medium mt-1">
                            @if ($designation->is_active)
                                <span class="hrms-badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="hrms-badge-danger">{{ __('Inactive') }}</span>
                            @endif
                        </p>
                    </div>
                    @if ($designation->description)
                        <div class="sm:col-span-2">
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Description') }}</flux:text>
                            <p class="text-sm text-zinc-700 dark:text-zinc-300 mt-1">{{ $designation->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
