<x-layouts::app :title="__('Edit Salary')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('salaries.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Edit Salary') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Update employee compensation') }}</flux:subheading>
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
            <form method="POST" action="{{ route('salaries.update', $salary) }}" class="flex flex-col gap-6">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:select name="user_id" :label="__('Employee')" required>
                        <option value="">{{ __('Select employee...') }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id', $salary->user_id) == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </flux:select>

                    @if ($companies->count() > 1)
                        <flux:select name="company_id" :label="__('Company')" required>
                            <option value="">{{ __('Select company...') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('company_id', $salary->company_id) == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </flux:select>
                    @else
                        <input type="hidden" name="company_id" value="{{ $companies->first()->id ?? '' }}">
                    @endif

                    <flux:select name="department_id" :label="__('Department')">
                        <option value="">{{ __('Select department...') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $salary->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:select name="designation_id" :label="__('Designation')">
                        <option value="">{{ __('Select designation...') }}</option>
                        @foreach ($designations as $desig)
                            <option value="{{ $desig->id }}" @selected(old('designation_id', $salary->designation_id) == $desig->id)>{{ $desig->title }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select name="pay_frequency" :label="__('Pay Frequency')" required>
                        <option value="monthly" @selected(old('pay_frequency', $salary->pay_frequency) == 'monthly')>{{ __('Monthly') }}</option>
                        <option value="bi_weekly" @selected(old('pay_frequency', $salary->pay_frequency) == 'bi_weekly')>{{ __('Bi-Weekly') }}</option>
                        <option value="weekly" @selected(old('pay_frequency', $salary->pay_frequency) == 'weekly')>{{ __('Weekly') }}</option>
                    </flux:select>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:input name="base_salary" :label="__('Base Salary')" type="number" step="0.01" :value="old('base_salary', $salary->base_salary)" required />
                    <flux:input name="allowances" :label="__('Allowances')" type="number" step="0.01" :value="old('allowances', $salary->allowances)" />
                    <flux:input name="deductions" :label="__('Deductions')" type="number" step="0.01" :value="old('deductions', $salary->deductions)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:input name="currency" :label="__('Currency')" :value="old('currency', $salary->currency)" />
                    <flux:input name="effective_from" :label="__('Effective From')" type="date" :value="old('effective_from', $salary->effective_from?->format('Y-m-d'))" required />
                    <flux:input name="effective_until" :label="__('Effective Until')" type="date" :value="old('effective_until', $salary->effective_until?->format('Y-m-d'))" />
                </div>

                <flux:select name="status" :label="__('Status')" required>
                    <option value="active" @selected(old('status', $salary->status) == 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(old('status', $salary->status) == 'inactive')>{{ __('Inactive') }}</option>
                    <option value="revised" @selected(old('status', $salary->status) == 'revised')>{{ __('Revised') }}</option>
                </flux:select>

                <flux:textarea name="notes" :label="__('Notes')" :value="old('notes', $salary->notes)" rows="2" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('salaries.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">{{ __('Update Salary') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
