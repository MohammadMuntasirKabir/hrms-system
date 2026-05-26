<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased">
        <!-- Blurry background -->
        <div class="fixed inset-0 -z-10 overflow-hidden bg-[#070a14]">
            <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-hrms-600/20 blur-[120px]"></div>
            <div class="absolute top-[40%] right-[-15%] w-[600px] h-[600px] rounded-full bg-purple-600/15 blur-[140px]"></div>
            <div class="absolute bottom-[-20%] left-[30%] w-[400px] h-[400px] rounded-full bg-blue-500/10 blur-[100px]"></div>
        </div>

        <div class="relative flex min-h-dvh flex-col lg:grid lg:grid-cols-2">

            <!-- Left: About / Branding Panel -->
            <!-- On mobile: shown at top, scrollable. On lg+: full-height side panel. -->
            <div class="relative flex flex-col justify-between p-8 sm:p-12 lg:p-12 xl:p-16 lg:dark:border-e lg:dark:border-white/5 bg-gradient-to-br from-[#0c1222]/95 via-[#0f1a30]/90 to-[#0a0f1e]/95 backdrop-blur-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-hrms-900/40 via-transparent to-purple-900/20"></div>

                <!-- Decorative orbs (hidden on mobile for performance) -->
                <div class="absolute top-0 right-0 w-80 h-80 bg-hrms-500/10 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/3 hidden lg:block"></div>
                <div class="absolute bottom-0 left-0 w-60 h-60 bg-purple-500/10 rounded-full blur-[80px] translate-y-1/3 -translate-x-1/4 hidden lg:block"></div>

                <!-- Top: Logo -->
                <a href="{{ route('dashboard') }}" class="relative z-20 flex items-center gap-3 text-lg font-semibold text-white" wire:navigate>
                    <span class="flex h-10 w-10 lg:h-11 lg:w-11 items-center justify-center rounded-xl bg-white/10 backdrop-blur-md border border-white/10 shadow-lg">
                        <x-app-logo-icon class="h-5 lg:h-6 fill-current text-white" />
                    </span>
                    HRMS
                </a>

                <!-- Center: About content -->
                <div class="relative z-20 my-6 lg:my-auto">
                    <flux:heading size="lg" level="h1" class="text-white leading-tight lg:text-xl">
                        Human Resource<br>Management System
                    </flux:heading>
                    <flux:text class="mt-3 text-blue-100/80 leading-relaxed text-sm lg:mt-4 lg:max-w-md">
                        Streamline your workforce operations across multiple companies and branches. Manage employees, roles, payroll, and organizational structure — all from a single platform.
                    </flux:text>

                    <!-- Feature list: 2-col on mobile, single col on desktop -->
                    <div class="mt-5 grid grid-cols-1 gap-2.5 lg:mt-8 lg:space-y-3.5">
                        <div class="flex items-center gap-2.5 text-sm text-blue-100/70">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 border border-white/10">
                                <flux:icon name="building-office" class="size-3.5 text-emerald-400" />
                            </span>
                            <span>Multi-company &amp; branch hierarchy</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-blue-100/70">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 border border-white/10">
                                <flux:icon name="users" class="size-3.5 text-emerald-400" />
                            </span>
                            <span>Role-based access control (RBAC)</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-blue-100/70">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 border border-white/10">
                                <flux:icon name="shield-check" class="size-3.5 text-emerald-400" />
                            </span>
                            <span>Granular permissions per role</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-blue-100/70">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 border border-white/10">
                                <flux:icon name="chart-bar" class="size-3.5 text-emerald-400" />
                            </span>
                            <span>Employee lifecycle management</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-blue-100/70">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 border border-white/10">
                                <flux:icon name="document-text" class="size-3.5 text-emerald-400" />
                            </span>
                            <span>Contract &amp; salary tracking</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-blue-100/70">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 border border-white/10">
                                <flux:icon name="user-plus" class="size-3.5 text-emerald-400" />
                            </span>
                            <span>Job applicant &amp; recruitment pipeline</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Copyright -->
                <div class="relative z-20 hidden lg:block">
                    <div class="border-t border-white/10 pt-4">
                        <flux:text class="text-xs text-blue-200/40">
                            &copy; {{ date('Y') }} HRMS. All rights reserved.
                        </flux:text>
                    </div>
                </div>
            </div>

            <!-- Right: Auth Form Panel -->
            <div class="flex flex-1 items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
                <div class="w-full max-w-[420px]">
                    <!-- Mobile-only: compact logo above form -->
                    <div class="flex items-center justify-center gap-2 mb-6 lg:hidden">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-hrms-600">
                            <x-app-logo-icon class="size-4 fill-current text-white" />
                        </span>
                        <span class="text-lg font-semibold text-white">HRMS</span>
                    </div>

                    <!-- Form card -->
                    <div class="rounded-2xl border border-white/10 bg-[#0d1117]/80 backdrop-blur-xl shadow-2xl shadow-black/40 p-5 sm:p-8">
                        {{ $slot }}
                    </div>

                    <!-- Mobile-only: copyright below form -->
                    <div class="mt-6 text-center lg:hidden">
                        <flux:text class="text-xs text-zinc-500">
                            &copy; {{ date('Y') }} HRMS. All rights reserved.
                        </flux:text>
                    </div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
