<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                placeholder="you@company.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Send reset link') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-500 dark:text-zinc-400 pt-2">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate class="text-hrms-600 dark:text-hrms-400 font-medium hover:underline">{{ __('sign in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
