<x-layouts::app :title="__('Add Designation')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('designations.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ __('Add Designation') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Create a new job title and hierarchy level') }}</flux:subheading>
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
            <form method="POST" action="{{ route('designations.store') }}" class="flex flex-col gap-6">
                @csrf

                <flux:input name="title" :label="__('Designation Title')" :value="old('title')" required placeholder="e.g. Senior Software Engineer" />

                <flux:textarea name="description" :label="__('Description')" :value="old('description')" placeholder="Brief description of the role..." rows="3" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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

                    <flux:select name="department_id" :label="__('Department')">
                        <option value="">{{ __('No department') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:select name="level" :label="__('Hierarchy Level')" required>
                    <option value="">{{ __('Select level...') }}</option>
                    @for ($i = 0; $i <= 10; $i++)
                        @php $labels = ['Entry Level', 'Mid Level', 'Senior', 'Lead', 'Manager', 'Director', 'VP', 'SVP', 'EVP', 'C-Level', 'Owner / Founder']; @endphp
                        <option value="{{ $i }}" @selected(old('level') == $i)>{{ $i }} — {{ $labels[$i] }}</option>
                    @endfor
                </flux:select>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button :href="route('designations.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Create Designation') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
