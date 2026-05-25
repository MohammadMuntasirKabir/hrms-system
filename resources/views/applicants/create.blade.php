<x-layouts::app :title="__('Add Applicant')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('applicants.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Add Applicant') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Register a new job applicant') }}</flux:subheading>
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
            <form method="POST" action="{{ route('applicants.store') }}" class="flex flex-col gap-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="first_name" :label="__('First Name')" :value="old('first_name')" required placeholder="John" />
                    <flux:input name="last_name" :label="__('Last Name')" :value="old('last_name')" required placeholder="Doe" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="email" :label="__('Email')" type="email" :value="old('email')" required placeholder="john@example.com" />
                    <flux:input name="phone" :label="__('Phone')" :value="old('phone')" placeholder="+880 1XXX-XXXXXX" />
                </div>

                <flux:input name="address" :label="__('Address')" :value="old('address')" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="city" :label="__('City')" :value="old('city')" placeholder="Dhaka" />
                    <flux:input name="country" :label="__('Country Code')" :value="old('country', 'BD')" placeholder="BD" maxlength="2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @if ($companies->count() > 1)
                        <flux:select name="company_id" :label="__('Company')" required>
                            <option value="">{{ __('Select company...') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </flux:select>
                    @else
                        <input type="hidden" name="company_id" value="{{ $companies->first()->id ?? '' }}">
                    @endif

                    <flux:select name="department_id" :label="__('Department')">
                        <option value="">{{ __('Select department...') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select name="designation_id" :label="__('Designation')">
                        <option value="">{{ __('Select designation...') }}</option>
                        @foreach ($designations as $desig)
                            <option value="{{ $desig->id }}" @selected(old('designation_id') == $desig->id)>{{ $desig->title }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:input name="expected_salary" :label="__('Expected Salary')" type="number" step="0.01" :value="old('expected_salary')" placeholder="0.00" />
                    <flux:input name="currency" :label="__('Currency')" :value="old('currency', 'BDT')" placeholder="BDT" />
                    <flux:input name="available_from" :label="__('Available From')" type="date" :value="old('available_from')" />
                </div>

                <flux:input name="source" :label="__('Source')" :value="old('source')" placeholder="e.g. Website, LinkedIn, Referral" />

                <flux:textarea name="cover_letter" :label="__('Cover Letter')" :value="old('cover_letter')" rows="4" placeholder="Applicant's cover letter..." />

                <flux:textarea name="notes" :label="__('Notes')" :value="old('notes')" rows="2" placeholder="Internal notes..." />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('applicants.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Add Applicant') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
