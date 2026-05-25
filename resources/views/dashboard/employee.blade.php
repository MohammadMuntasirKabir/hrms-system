<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __("Here's what's happening across your organization today.") }}</flux:subheading>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="building-office" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Company') }}</flux:text>
                            <p class="text-xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->company?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="identification" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Employee ID') }}</flux:text>
                            <p class="text-xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->employee_id ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-emerald-50 dark:bg-emerald-900/30"><flux:icon name="user-group" class="size-5 text-emerald-600 dark:text-emerald-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Role') }}</flux:text>
                            <p class="text-xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->getRoleNames()->first() ? ucwords(str_replace('_', ' ', auth()->user()->getRoleNames()->first())) : '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hrms-card">
            <div class="hrms-card-body">
                <flux:heading size="lg" level="2" class="mb-4 text-zinc-900 dark:text-white">{{ __('Quick Actions') }}</flux:heading>
                <div class="flex flex-wrap gap-3">
                    <flux:button :href="route('profile.edit')" variant="outline" wire:navigate icon="user-circle">{{ __('Edit Profile') }}</flux:button>
                </div>
            </div>
        </div>

        <div class="hrms-card">
            <div class="hrms-card-body">
                <div class="flex items-center gap-3 mb-3">
                    <flux:icon name="information-circle" class="size-5 text-blue-500" />
                    <flux:heading size="md" class="text-zinc-900 dark:text-white">{{ __('Getting Started') }}</flux:heading>
                </div>
                <flux:text class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    {{ __('Welcome to the HRMS platform. Use the navigation sidebar to manage your profile and view information. Contact your administrator if you need additional permissions.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
