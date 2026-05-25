<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile
        :name="auth()->user()->name"
        :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
    />

    <flux:menu>
        <div class="flex items-center gap-3 px-2 py-2 text-start text-sm">
            <flux:avatar
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
                size="sm"
                class="bg-hrms-600 text-white"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate text-zinc-900 dark:text-white font-semibold">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="user-circle" wire:navigate>
                {{ __('My Profile') }}
            </flux:menu.item>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
        </flux:menu.radio.group>

        <flux:menu.separator />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer text-red-600 dark:text-red-400"
                data-test="logout-button"
            >
                {{ __('Sign out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
