<x-layouts::app :title="__('Add Employee')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('users.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Add New Employee') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Create a new employee account') }}</flux:subheading>
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
            <form method="POST" action="{{ route('users.store') }}" class="flex flex-col gap-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="name" :label="__('Full Name')" :value="old('name')" required placeholder="John Doe" />
                    <flux:input name="email" :label="__('Email Address')" type="email" :value="old('email')" required placeholder="john@company.com" />
                    <flux:input name="employee_id" :label="__('Employee ID')" :value="old('employee_id')" placeholder="EMP-001" />
                    <flux:input name="job_title" :label="__('Job Title')" :value="old('job_title')" placeholder="Software Engineer" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @if ($companies->count() > 0)
                        <flux:select name="company_id" :label="__('Company')" required>
                            <option value="">{{ __('Select company...') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </flux:select>
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

                <flux:select name="role" :label="__('Role')" required>
                    <option value="">{{ __('Select role...') }}</option>
                    @foreach ($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role') == $key)>{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:separator />

                <div>
                    <flux:heading size="sm" class="mb-3 text-zinc-900 dark:text-white">{{ __('Set Password') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:input name="password" :label="__('Password')" type="password" required viewable />
                        <flux:input name="password_confirmation" :label="__('Confirm Password')" type="password" required viewable />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('users.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Create Employee') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
