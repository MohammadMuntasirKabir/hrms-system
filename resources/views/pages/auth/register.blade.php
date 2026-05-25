<x-layouts::auth :title="__('Create account')">
    <div class="flex flex-col gap-5">
        <x-auth-header :title="__('Create your account')" :description="__('Get started with the HR Management System for your organization')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="name"
                :label="__('Full name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('John Doe')"
            />

            <flux:input
                name="email"
                :label="__('Work email')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="you@company.com"
            />

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Min. 8 characters')"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Re-enter password')"
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-500 pt-1">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate class="text-hrms-400 font-medium hover:text-hrms-300 hover:underline">{{ __('Sign in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
