<x-layouts::app :title=" __('Edit') . ' ' . $company->name">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('companies.show', $company)" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Edit Company') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ $company->name }}</flux:subheading>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon name="exclamation-circle" class="size-5 text-red-600 dark:text-red-400" />
                    <span class="font-medium text-red-700 dark:text-red-300">{{ __('Please fix the following errors:') }}</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="hrms-form-card max-w-2xl">
            <form method="POST" action="{{ route('companies.update', $company) }}" class="flex flex-col gap-6">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="name" :label="__('Company Name')" :value="old('name', $company->name)" required />
                    <flux:input name="slug" :label="__('Slug')" :value="old('slug', $company->slug)" required />
                </div>

                <flux:input name="domain" :label="__('Domain')" :value="old('domain', $company->domain)" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="country" :label="__('Country Code')" :value="old('country', $company->country)" />
                    <flux:input name="timezone" :label="__('Timezone')" :value="old('timezone', $company->timezone)" />
                </div>

                <flux:select name="parent_company_id" :label="__('Parent Company')">
                    <option value="">{{ __('None (Top-level company)') }}</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" @selected(old('parent_company_id', $company->parent_company_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </flux:select>

                <flux:checkbox name="is_active" :label="__('Active')" :checked="old('is_active', $company->is_active)" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('companies.show', $company)" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">{{ __('Update Company') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
