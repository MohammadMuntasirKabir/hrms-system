<x-layouts::app :title="__('Edit Contract')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('contracts.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Edit Contract') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Update contract details') }}</flux:subheading>
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
            <form method="POST" action="{{ route('contracts.update', $contract) }}" class="flex flex-col gap-6">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:select name="user_id" :label="__('Employee')" required>
                        <option value="">{{ __('Select employee...') }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id', $contract->user_id) == $u->id)>{{ $u->name }} — {{ $u->employee_id ?? 'No ID' }}</option>
                        @endforeach
                    </flux:select>

                    @if ($companies->count() > 1)
                        <flux:select name="company_id" :label="__('Company')" required>
                            <option value="">{{ __('Select company...') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('company_id', $contract->company_id) == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </flux:select>
                    @else
                        <input type="hidden" name="company_id" value="{{ $companies->first()->id ?? '' }}">
                    @endif

                    <flux:select name="department_id" :label="__('Department')">
                        <option value="">{{ __('No department') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $contract->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="position" :label="__('Position')" :value="old('position', $contract->position)" required />
                    <flux:select name="contract_type" :label="__('Contract Type')" required>
                        <option value="">{{ __('Select type...') }}</option>
                        <option value="full_time" @selected(old('contract_type', $contract->contract_type) == 'full_time')>{{ __('Full Time') }}</option>
                        <option value="part_time" @selected(old('contract_type', $contract->contract_type) == 'part_time')>{{ __('Part Time') }}</option>
                        <option value="contract" @selected(old('contract_type', $contract->contract_type) == 'contract')>{{ __('Contract') }}</option>
                        <option value="internship" @selected(old('contract_type', $contract->contract_type) == 'internship')>{{ __('Internship') }}</option>
                        <option value="freelance" @selected(old('contract_type', $contract->contract_type) == 'freelance')>{{ __('Freelance') }}</option>
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="start_date" :label="__('Start Date')" type="date" :value="old('start_date', $contract->start_date?->format('Y-m-d'))" required />
                    <flux:input name="end_date" :label="__('End Date')" type="date" :value="old('end_date', $contract->end_date?->format('Y-m-d'))" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:input name="salary" :label="__('Salary')" type="number" step="0.01" :value="old('salary', $contract->salary)" />
                    <flux:input name="currency" :label="__('Currency')" :value="old('currency', $contract->currency)" />
                    <flux:select name="status" :label="__('Status')" required>
                        <option value="active" @selected(old('status', $contract->status) == 'active')>{{ __('Active') }}</option>
                        <option value="draft" @selected(old('status', $contract->status) == 'draft')>{{ __('Draft') }}</option>
                        <option value="terminated" @selected(old('status', $contract->status) == 'terminated')>{{ __('Terminated') }}</option>
                        <option value="expired" @selected(old('status', $contract->status) == 'expired')>{{ __('Expired') }}</option>
                    </flux:select>
                </div>

                <flux:textarea name="notes" :label="__('Notes')" :value="old('notes', $contract->notes)" rows="3" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('contracts.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">{{ __('Update Contract') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
