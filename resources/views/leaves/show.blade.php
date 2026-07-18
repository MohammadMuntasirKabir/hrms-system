<x-layouts::app :title="__('Leave Request')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('leaves.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Leave Request') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ $leave->user->name }}</flux:subheading>
            </div>
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

        <div class="grid auto-rows-min gap-4 lg:grid-cols-3">
            <div class="hrms-card lg:col-span-2">
                <div class="hrms-card-body flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">{{ __('Details') }}</flux:heading>
                        @if ($leave->isPending)
                            <span class="hrms-badge-warning">{{ __('Pending') }}</span>
                        @elseif ($leave->isApproved)
                            <span class="hrms-badge-success">{{ __('Approved') }}</span>
                        @elseif ($leave->isRejected)
                            <span class="hrms-badge-danger">{{ __('Rejected') }}</span>
                        @else
                            <span class="hrms-badge-neutral">{{ __('Cancelled') }}</span>
                        @endif
                    </div>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Employee') }}</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium">{{ $leave->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Company') }}</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium">{{ $leave->company?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium">{{ ucfirst($leave->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Total Days') }}</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium">{{ $leave->total_days }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Start Date') }}</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium">{{ $leave->start_date->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('End Date') }}</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium">{{ $leave->end_date->format('M d, Y') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Reason') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $leave->reason ?? '—' }}</dd>
                        </div>
                        @if ($leave->admin_note)
                            <div class="sm:col-span-2">
                                <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Decision Note') }}</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $leave->admin_note }}</dd>
                            </div>
                        @endif
                        @if ($leave->approver)
                            <div class="sm:col-span-2">
                                <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Decided By') }}</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $leave->approver->name }} @if($leave->decided_at)· {{ $leave->decided_at->format('M d, Y H:i') }}@endif</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="hrms-card">
                <div class="hrms-card-body flex flex-col gap-3">
                    <flux:heading size="lg" level="2" class="text-zinc-900 dark:text-white">{{ __('Actions') }}</flux:heading>

                    @can('leave.approve')
                        @if ($leave->isPending)
                            <form method="POST" action="{{ route('leaves.approve', $leave) }}" class="flex flex-col gap-3">
                                @csrf
                                <flux:textarea name="admin_note" :label="__('Approval Note (optional)')" :value="old('admin_note')" rows="2" />
                                <flux:button type="submit" variant="primary" icon="check" class="w-full">{{ __('Approve') }}</flux:button>
                            </form>
                            <form method="POST" action="{{ route('leaves.reject', $leave) }}" class="flex flex-col gap-3">
                                @csrf
                                <flux:textarea name="admin_note" :label="__('Rejection Reason')" :value="old('admin_note')" rows="2" required />
                                <flux:button type="submit" variant="danger" icon="x-mark" class="w-full">{{ __('Reject') }}</flux:button>
                            </form>
                        @endif
                    @endcan

                    @if ($leave->isPending && ($leave->user_id === auth()->id() || auth()->user()->can('leave.manage')))
                        <form method="POST" action="{{ route('leaves.cancel', $leave) }}">
                            @csrf
                            <flux:button type="submit" variant="outline" icon="no-symbol" class="w-full">{{ __('Cancel Request') }}</flux:button>
                        </form>
                    @endif

                    @can('leave.manage')
                        <flux:button :href="route('leaves.edit', $leave)" variant="outline" icon="pencil" wire:navigate class="w-full">{{ __('Edit') }}</flux:button>
                        @if ($leave->isPending)
                            <form method="POST" action="{{ route('leaves.destroy', $leave) }}" onsubmit="return confirm('Delete this leave request?');">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" variant="subtle" icon="trash" class="w-full text-red-600 dark:text-red-400">{{ __('Delete') }}</flux:button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
