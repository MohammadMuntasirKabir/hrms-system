<x-layouts::auth :title="__('Sign in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Welcome back')" :description="__('Sign in to your HRMS account to manage your organization')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@company.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Enter your password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0 text-hrms-600 dark:text-hrms-400 hover:underline" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Remember this device')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Sign in') }}
            </flux:button>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-500 dark:text-zinc-400 pt-2">
                <span>{{ __("Don't have an account?") }}</span>
                <flux:link :href="route('register')" wire:navigate class="text-hrms-600 dark:text-hrms-400 font-medium hover:underline">{{ __('Create one') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
