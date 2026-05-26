<x-layouts::app :title="__('Edit Employee')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('users.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Edit Employee') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Update employee information') }}</flux:subheading>
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

        <div class="hrms-form-card max-w-2xl">
            <form method="POST" action="{{ route('users.update', $user) }}" class="flex flex-col gap-6">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="name" :label="__('Full Name')" :value="old('name', $user->name)" required />
                    <flux:input name="email" :label="__('Email Address')" type="email" :value="old('email', $user->email)" required />
                    <flux:input name="employee_id" :label="__('Employee ID')" :value="old('employee_id', $user->employee_id)" />
                    <flux:input name="job_title" :label="__('Job Title')" :value="old('job_title', $user->job_title)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @if ($companies->count() > 0)
                        <flux:select name="company_id" :label="__('Company')" required>
                            <option value="">{{ __('Select company...') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('company_id', $user->company_id) == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </flux:select>
                    @endif

                    <flux:select name="department_id" :label="__('Department')">
                        <option value="">{{ __('No department') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $user->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select name="designation_id" :label="__('Designation')">
                        <option value="">{{ __('No designation') }}</option>
                        @foreach ($designations as $desig)
                            <option value="{{ $desig->id }}" @selected(old('designation_id', $user->designation_id) == $desig->id)>{{ $desig->title }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:select name="role" :label="__('Role')" required>
                    <option value="">{{ __('Select role...') }}</option>
                    @foreach ($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', $user->getRoleNames()->first()) == $key)>{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:checkbox name="is_active" :label="__('Active Employee')" :checked="old('is_active', $user->is_active)" />

                <flux:separator />

                <div>
                    <flux:heading size="sm" class="mb-1 text-zinc-900 dark:text-white">{{ __('Change Password') }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Leave blank to keep current password.') }}</flux:text>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:input name="password" :label="__('New Password')" type="password" viewable />
                        <flux:input name="password_confirmation" :label="__('Confirm New Password')" type="password" viewable />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('users.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">{{ __('Update Employee') }}</flux:button>
                </div>
            </form>
        </div>

        {{-- Admin Management Section — Only visible to super admin --}}
        @if (auth()->user()->isSuperAdmin())
            <div class="hrms-form-card max-w-2xl">
                <flux:heading size="lg" class="mb-4 text-zinc-900 dark:text-white">{{ __('Admin Management') }}</flux:heading>

                {{-- Transfer Super Admin --}}
                @if ($user->isSuperAdmin())
                    <div class="mb-6">
                        <flux:heading size="sm" class="mb-2 text-zinc-900 dark:text-white">{{ __('Transfer Super Admin') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-3">
                            {{ __('Transfer your super admin status to another user. You will lose super admin privileges. This action cannot be undone by you.') }}
                        </flux:text>
                        <form method="POST" action="{{ route('admin.transfer-superadmin') }}" class="flex flex-col gap-3">
                            @csrf
                            <flux:select name="new_superadmin_id" :label="__('New Super Admin')" required>
                                <option value="">{{ __('Select user...') }}</option>
                                @foreach (\App\Models\User::where('is_active', true)->where('id', '!=', $user->id)->orderBy('name')->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) — {{ $u->company?->name ?? 'No company' }}</option>
                                @endforeach
                            </flux:select>
                            <div>
                                <flux:button type="submit" variant="danger" icon="arrow-right" onclick="return confirm('{{ __('Are you sure? You will lose super admin status!') }}')">
                                    {{ __('Transfer Super Admin') }}
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Company Admin Assignment --}}
                @if ($user->company_id && ! $user->isSuperAdmin())
                    <div class="mb-6">
                        <flux:heading size="sm" class="mb-2 text-zinc-900 dark:text-white">{{ __('Company Admin') }}</flux:heading>

                        @php
                            $companyAdmin = \App\Models\User::where('company_id', $user->company_id)
                                ->where('id', '!=', $user->id)
                                ->whereHas('roles', fn ($q) => $q->where('name', 'company_admin'))
                                ->first();
                        @endphp

                        @if ($companyAdmin)
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">
                                {{ __('Current admin of :company is :name.', ['company' => $user->company->name, 'name' => $companyAdmin->name]) }}
                            </flux:text>
                            <form method="POST" action="{{ route('admin.remove-company-admin') }}" class="mb-3">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $companyAdmin->id }}">
                                <flux:button type="submit" variant="danger" icon="x-mark" onclick="return confirm('{{ __('Remove :name as admin of :company?', ['name' => $companyAdmin->name, 'company' => $user->company->name]) }}')">
                                    {{ __('Remove :name as Admin', ['name' => $companyAdmin->name]) }}
                                </flux:button>
                            </form>
                        @else
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">
                                {{ __('No company admin assigned to :company.', ['company' => $user->company->name]) }}
                            </flux:text>
                        @endif>

                        @if (! $user->isCompanyAdmin())
                            <form method="POST" action="{{ route('admin.assign-company-admin') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <flux:button type="submit" variant="primary" icon="shield-check">
                                    {{ __('Make :name Admin of :company', ['name' => $user->name, 'company' => $user->company->name]) }}
                                </flux:button>
                            </form>
                        @else
                            <flux:text class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">
                                {{ __('This user is the admin of :company.', ['company' => $user->company->name]) }}
                            </flux:text>
                            <form method="POST" action="{{ route('admin.remove-company-admin') }}" class="mt-3">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <flux:button type="submit" variant="danger" icon="x-mark" onclick="return confirm('{{ __('Remove :name as admin of :company?', ['name' => $user->name, 'company' => $user->company->name]) }}')">
                                    {{ __('Remove as Admin') }}
                                </flux:button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts::app>
