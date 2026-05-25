@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center mb-6">
    <div class="flex flex-col items-center gap-2 mb-2">
        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-hrms-600 shadow-lg shadow-hrms-600/25">
            <x-app-logo-icon class="size-7 fill-current text-white" />
        </span>
    </div>
    <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $title }}</flux:heading>
    <flux:subheading class="mt-1 max-w-xs mx-auto">{{ $description }}</flux:subheading>
</div>
