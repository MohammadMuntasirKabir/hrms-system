@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="HRMS" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-hrms-600 text-white">
            <x-app-logo-icon class="size-5 fill-current text-white" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="HRMS" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-hrms-600 text-white">
            <x-app-logo-icon class="size-5 fill-current text-white" />
        </x-slot>
    </flux:brand>
@endif
