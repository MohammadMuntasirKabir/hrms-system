@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center mb-2">
    <div class="flex flex-col items-center gap-2 mb-3">
        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-hrms-600/20 border border-hrms-500/20 shadow-lg shadow-hrms-600/10">
            <x-app-logo-icon class="size-6 fill-current text-hrms-400" />
        </span>
    </div>
    <flux:heading size="xl" class="text-white">{{ $title }}</flux:heading>
    <flux:subheading class="mt-1.5 max-w-xs mx-auto text-zinc-400">{{ $description }}</flux:subheading>
</div>
