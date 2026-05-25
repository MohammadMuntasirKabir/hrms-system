<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-[#0f1629] bg-[#0c1222] dark:border-[#0f1629] dark:bg-[#0c1222]">
            <flux:sidebar.header class="border-b border-white/10">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <!-- Company Filter (Super Admin) -->
            @if(auth()->check() && auth()->user()->isSuperAdmin())
                @php
                    $sidebarCompanies = \App\Models\Company::where('is_active', true)->orderBy('name')->get();
                @endphp
                <div class="px-3 pt-3">
                    <form method="GET" action="{{ url()->current() }}" id="company-filter-form">
                        @foreach(request()->except('company_id') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <flux:select name="company_id" onchange="this.form.submit()" size="sm">
                            <option value="">{{ __('All Companies') }}</option>
                            @foreach($sidebarCompanies as $company)
                                <option value="{{ $company->id }}" @selected(session('filter_company_id') == $company->id)>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </flux:select>
                    </form>
                </div>
            @elseif(auth()->check())
                <div class="px-3 pt-3">
                    <div class="flex items-center gap-2 text-xs text-white/40">
                        <flux:icon name="building-office" class="size-3" />
                        <span>{{ auth()->user()->company?->name }}</span>
                        @if(auth()->user()->company?->isSubCompany())
                            <span class="text-white/20">— {{ auth()->user()->company?->parentCompany?->name }}</span>
                        @endif
                    </div>
                </div>
            @endif

            <flux:sidebar.nav class="px-3 pt-3">
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            @can('companies.view')
                <flux:sidebar.nav class="px-3">
                    <flux:sidebar.group :heading="__('Organization')" class="grid">
                        <flux:sidebar.item icon="building-office" :href="route('companies.index', session('filter_company_id') ? ['company_id' => session('filter_company_id')] : [])" :current="request()->routeIs('companies.*')" wire:navigate>
                            {{ __('Companies') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office-2" :href="route('departments.index', session('filter_company_id') ? ['company_id' => session('filter_company_id')] : [])" :current="request()->routeIs('departments.*')" wire:navigate>
                            {{ __('Departments') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                </flux:sidebar.nav>
            @endcan

            @can('users.view')
                <flux:sidebar.nav class="px-3">
                    <flux:sidebar.group :heading="__('People')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('users.index', session('filter_company_id') ? ['company_id' => session('filter_company_id')] : [])" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ __('Employees') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="briefcase" :href="route('designations.index', session('filter_company_id') ? ['company_id' => session('filter_company_id')] : [])" :current="request()->routeIs('designations.*')" wire:navigate>
                            {{ __('Designations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('contracts.index', session('filter_company_id') ? ['company_id' => session('filter_company_id')] : [])" :current="request()->routeIs('contracts.*')" wire:navigate>
                            {{ __('Contracts') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                </flux:sidebar.nav>
            @endcan

            @can('applicants.view')
                <flux:sidebar.nav class="px-3">
                    <flux:sidebar.group :heading="__('Recruitment')" class="grid">
                        <flux:sidebar.item icon="user-plus" :href="route('applicants.index', session('filter_company_id') ? ['company_id' => session('filter_company_id')] : [])" :current="request()->routeIs('applicants.*')" wire:navigate>
                            {{ __('Applicants') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                </flux:sidebar.nav>
            @endcan

            @can('payroll.view')
                <flux:sidebar.nav class="px-3">
                    <flux:sidebar.group :heading="__('Finance')" class="grid">
                        <flux:sidebar.item icon="currency-dollar" :href="route('salaries.index', session('filter_company_id') ? ['company_id' => session('filter_company_id')] : [])" :current="request()->routeIs('salaries.*')" wire:navigate>
                            {{ __('Salaries') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                </flux:sidebar.nav>
            @endcan

            <flux:spacer />

            <flux:sidebar.nav class="px-3 pb-4 border-t border-white/10 pt-3">
                <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.*')" wire:navigate>
                    {{ __('Settings') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <div class="hidden lg:block px-3 pb-3 border-t border-white/10 pt-3">
                <x-desktop-user-menu :name="auth()->user()->name" />
            </div>
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" class="bg-hrms-600 text-white" />
                <flux:menu>
                    <div class="flex items-center gap-3 px-2 py-2 text-start text-sm">
                        <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" class="bg-hrms-600 text-white" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate text-zinc-900 dark:text-white font-semibold">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="user-circle" wire:navigate>{{ __('My Profile') }}</flux:menu.item>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer text-red-600 dark:text-red-400" data-test="logout-button">{{ __('Sign out') }}</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group><flux:toast /></flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
