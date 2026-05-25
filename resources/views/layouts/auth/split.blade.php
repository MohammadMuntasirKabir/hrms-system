<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <!-- Left branding panel -->
            <div class="relative hidden h-full flex-col p-10 lg:flex dark:border-e dark:border-neutral-800 hrms-auth-gradient">
                <div class="absolute inset-0 bg-gradient-to-br from-hrms-900/90 via-hrms-800/80 to-hrms-700/70"></div>

                <!-- Decorative elements -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

                <a href="{{ route('dashboard') }}" class="relative z-20 flex items-center gap-3 text-lg font-semibold text-white" wire:navigate>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm border border-white/20">
                        <x-app-logo-icon class="h-6 fill-current text-white" />
                    </span>
                    HRMS
                </a>

                <div class="relative z-20 mt-auto space-y-8">
                    <div>
                        <flux:heading size="xl" class="text-white leading-tight">Human Resource<br>Management System</flux:heading>
                        <flux:text class="mt-3 text-blue-100 leading-relaxed">
                            Streamline your workforce operations across multiple companies and branches. Manage employees, roles, and organizational structure — all from a single platform.
                        </flux:text>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-sm text-blue-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 backdrop-blur-sm">
                                <flux:icon name="building-office" class="size-4 text-emerald-300" />
                            </span>
                            <span>Multi-company & branch hierarchy</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-blue-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 backdrop-blur-sm">
                                <flux:icon name="users" class="size-4 text-emerald-300" />
                            </span>
                            <span>Role-based access control (RBAC)</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-blue-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 backdrop-blur-sm">
                                <flux:icon name="shield-check" class="size-4 text-emerald-300" />
                            </span>
                            <span>Granular permissions per role</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-blue-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 backdrop-blur-sm">
                                <flux:icon name="chart-bar" class="size-4 text-emerald-300" />
                            </span>
                            <span>Employee lifecycle management</span>
                        </div>
                    </div>

                    <div class="border-t border-white/15 pt-4">
                        <flux:text class="text-xs text-blue-200/60">
                            &copy; {{ date('Y') }} HRMS. All rights reserved.
                        </flux:text>
                    </div>
                </div>
            </div>

            <!-- Right form panel -->
            <div class="w-full lg:p-8 bg-zinc-50 dark:bg-zinc-950">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[380px]">
                    <a href="{{ route('dashboard') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-hrms-600 shadow-lg shadow-hrms-600/25">
                            <x-app-logo-icon class="size-6 fill-current text-white" />
                        </span>
                        <span class="text-lg font-semibold text-zinc-900 dark:text-white">HRMS</span>
                    </a>
                    {{ $slot }}
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
