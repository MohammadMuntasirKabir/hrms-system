<x-layouts::app :title="__('Add Department')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('departments.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Add Department') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Create a new organizational department') }}</flux:subheading>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon name="exclamation-circle" class="size-5 text-red-600 dark:text-red-400" />
                    <span class="font-medium text-red-700 dark:text-red-300">{{ __('Please fix the following errors:') }}</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="hrms-form-card max-w-2xl">
            <form method="POST" action="{{ route('departments.store') }}" class="flex flex-col gap-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input name="name" :label="__('Department Name')" :value="old('name')" required placeholder="e.g. Engineering" />
                    <flux:input name="code" :label="__('Code')" :value="old('code')" placeholder="e.g. ENG" maxlength="20" />
                </div>

                <flux:textarea name="description" :label="__('Description')" :value="old('description')" placeholder="Department overview..." rows="3" />

                @if ($companies->count() > 1)
                    <flux:select name="company_id" :label="__('Company')" required>
                        <option value="">{{ __('Select company...') }}</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </flux:select>
                @else
                    <input type="hidden" name="company_id" value="{{ $companies->first()->id ?? '' }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:select name="parent_department_id" :label="__('Parent Department')">
                        <option value="">{{ __('None (Top Level)') }}</option>
                        @foreach ($parentDepartments as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_department_id') == $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select name="head_user_id" :label="__('Department Head')">
                        <option value="">{{ __('Select head...') }}</option>
                        @foreach ($heads as $head)
                            <option value="{{ $head->id }}" @selected(old('head_user_id') == $head->id)>{{ $head->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('departments.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Create Department') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
