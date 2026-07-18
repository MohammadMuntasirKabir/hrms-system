<x-layouts::app :title="__('New Leave Request')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('leaves.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('New Leave Request') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Submit a new employee leave request') }}</flux:subheading>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon name="exclamation-circle" class="size-5 text-red-600 dark:text-red-400" />
                    <span class="font-medium text-red-700 dark:text-red-300">{{ __('Please fix the following errors:') }}</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="hrms-form-card max-w-2xl">
            <form method="POST" action="{{ route('leaves.store') }}" class="flex flex-col gap-6">
                @csrf

                <flux:select name="user_id" :label="__('Employee')" required>
                    <option value="">{{ __('Select employee...') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('user_id') == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select name="type" :label="__('Leave Type')" required>
                    <option value="">{{ __('Select type...') }}</option>
                    @foreach (\App\Models\Leave::TYPES as $type)
                        <option value="{{ $type }}" @selected(old('type') == $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input type="date" name="start_date" :label="__('Start Date')" :value="old('start_date')" required />
                    <flux:input type="date" name="end_date" :label="__('End Date')" :value="old('end_date')" required />
                </div>

                <flux:textarea name="reason" :label="__('Reason')" :value="old('reason')" placeholder="{{ __('Optional reason...') }}" rows="3" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('leaves.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Submit Request') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
