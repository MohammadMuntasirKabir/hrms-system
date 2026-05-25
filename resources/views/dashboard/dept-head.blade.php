<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Department Head Dashboard') }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Company: :name', ['name' => auth()->user()->company?->name ?? '—']) }}</flux:subheading>
        </div>

        <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-blue-50 dark:bg-blue-900/30"><flux:icon name="users" class="size-5 text-blue-600 dark:text-blue-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Team Members') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ $stats['totalEmployees'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-amber-50 dark:bg-amber-900/30"><flux:icon name="clock" class="size-5 text-amber-600 dark:text-amber-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending Leave Requests') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">—</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hrms-stat-card hrms-card">
                <div class="hrms-card-body">
                    <div class="flex items-center gap-4">
                        <div class="hrms-stat-icon bg-purple-50 dark:bg-purple-900/30"><flux:icon name="building-office" class="size-5 text-purple-600 dark:text-purple-400" /></div>
                        <div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Department') }}</flux:text>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5">{{ auth()->user()->department?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hrms-card flex-1 min-h-0">
            <div class="hrms-card-body">
                <flux:heading size="lg" level="2" class="mb-4 text-zinc-900 dark:text-white">{{ __('Team Overview') }}</flux:heading>
                <div class="hrms-empty-state py-8">
                    <div class="hrms-empty-state-icon"><flux:icon name="user-group" class="size-7 text-zinc-400 dark:text-zinc-600" /></div>
                    <flux:text class="text-zinc-500 dark:text-zinc-400 mb-2">{{ __('Team management features coming soon.') }}</flux:text>
                    <flux:text class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('Leave requests, attendance tracking, and performance reviews will be available here.') }}</flux:text>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
