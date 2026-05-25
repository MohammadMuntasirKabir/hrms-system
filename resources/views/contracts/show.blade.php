<x-layouts::app :title="__('Contract Details')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('contracts.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div class="flex-1">
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $contract->position }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Contract for :name', ['name' => $contract->user->name]) }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                @can('contracts.edit')
                    <flux:button :href="route('contracts.edit', $contract)" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                @endcan
                @can('contracts.delete')
                    <form method="POST" action="{{ route('contracts.destroy', $contract) }}" class="inline">
                        @csrf @method('DELETE')
                        <flux:button type="submit" variant="danger" icon="trash" onclick="return confirm('{{ __('Delete this contract?') }}')">{{ __('Delete') }}</flux:button>
                    </form>
                @endcan
            </div>
        </div>

        @if ($contract->status === 'active' && $contract->is_expiring_soon)
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 p-4 flex items-center gap-3">
                <flux:icon name="clock" class="size-5 text-amber-600 dark:text-amber-400 shrink-0" />
                <span class="text-sm text-amber-700 dark:text-amber-300">{{ __('This contract expires in :days days (:date)', ['days' => $contract->end_date->diffInDays(now()), 'date' => $contract->end_date->format('M d, Y')]) }}</span>
            </div>
        @elseif ($contract->is_expired)
            <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 flex items-center gap-3">
                <flux:icon name="exclamation-circle" class="size-5 text-red-600 dark:text-red-400 shrink-0" />
                <span class="text-sm text-red-700 dark:text-red-300">{{ __('This contract expired on :date', ['date' => $contract->end_date->format('M d, Y')]) }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 hrms-card">
                <div class="hrms-card-body">
                    <flux:heading size="lg" class="mb-5 text-zinc-900 dark:text-white">{{ __('Contract Information') }}</flux:heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Employee') }}</flux:text>
                            <div class="flex items-center gap-3 mt-1.5">
                                <flux:avatar :name="$contract->user->name" size="sm" />
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $contract->user->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $contract->user->email }}</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Department') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $contract->department?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Designation') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $contract->user->designation?->title ?? '—' }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Position') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $contract->position }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Contract Type') }}</flux:text>
                            <p class="mt-1.5"><span class="hrms-badge-purple">{{ ucwords(str_replace('_', ' ', $contract->contract_type)) }}</span></p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Start Date') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $contract->start_date->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('End Date') }}</flux:text>
                            <p class="mt-1.5 font-medium {{ $contract->is_expired ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $contract->end_date ? $contract->end_date->format('F d, Y') : __('Ongoing (No end date)') }}
                            </p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Salary') }}</flux:text>
                            <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">
                                @if ($contract->salary){{ $contract->currency }} {{ number_format($contract->salary, 2) }}@else<span class="text-zinc-400">—</span>@endif
                            </p>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Status') }}</flux:text>
                            <p class="mt-1.5">
                                @if ($contract->status === 'active')
                                    @if ($contract->is_expiring_soon)<span class="hrms-badge-warning">{{ __('Expiring Soon') }}</span>
                                    @else<span class="hrms-badge-success">{{ __('Active') }}</span>@endif
                                @elseif ($contract->status === 'expired' || $contract->is_expired)<span class="hrms-badge-danger">{{ __('Expired') }}</span>
                                @elseif ($contract->status === 'terminated')<span class="hrms-badge-danger">{{ __('Terminated') }}</span>
                                @else<span class="hrms-badge-neutral">{{ ucfirst($contract->status) }}</span>@endif
                            </p>
                        </div>
                    </div>
                    @if ($contract->notes)
                        <flux:separator class="my-5" />
                        <div>
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Notes') }}</flux:text>
                            <p class="mt-1.5 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed whitespace-pre-wrap">{{ $contract->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Company') }}</flux:text>
                        <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $contract->company?->name ?? '—' }}</p>
                    </div>
                </div>
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Contract Duration') }}</flux:text>
                        @php $days = $contract->start_date->diffInDays($contract->end_date ?? now()); $months = round($days / 30); @endphp
                        <p class="mt-1.5 text-2xl font-bold text-zinc-900 dark:text-white">{{ $months }} <span class="text-sm font-normal text-zinc-500">months</span></p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $days }} {{ __('total days') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
