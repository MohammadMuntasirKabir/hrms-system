<x-layouts::app :title="$applicant->full_name">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('applicants.index')" variant="ghost" icon="arrow-left" wire:navigate class="shrink-0">{{ __('Back') }}</flux:button>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $applicant->full_name }}</flux:heading>
                    @php
                        $statusColors = ['pending' => 'warning', 'reviewing' => 'info', 'shortlisted' => 'purple', 'hired' => 'success', 'rejected' => 'danger'];
                    @endphp
                    <span class="hrms-badge-{{ $statusColors[$applicant->status] ?? 'neutral' }}">{{ ucfirst($applicant->status) }}</span>
                </div>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ $applicant->email }} · {{ $applicant->company?->name }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                @if (!$applicant->isHired() && !$applicant->isRejected() && auth()->user()->isSeniorMember())
                    <flux:button :href="route('applicants.edit', $applicant)" variant="outline" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
                @endif
                @if ($applicant->isHired() && $applicant->hiredAsUser)
                    <flux:button :href="route('users.show', $applicant->hiredAsUser)" variant="primary" icon="user" wire:navigate>{{ __('View Employee') }}</flux:button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 p-4 flex items-center gap-3">
                <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 flex items-center gap-3">
                <flux:icon name="exclamation-circle" class="size-5 text-red-600 dark:text-red-400 shrink-0" />
                <span class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-4">
                <div class="hrms-card">
                    <div class="hrms-card-body">
                        <flux:heading size="lg" class="mb-5 text-zinc-900 dark:text-white">{{ __('Applicant Information') }}</flux:heading>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Full Name') }}</flux:text>
                                <p class="mt-1.5 font-medium text-zinc-900 dark:text-white">{{ $applicant->full_name }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Email') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->email }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Phone') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->phone ?? '—' }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Location') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->city ? $applicant->city . ', ' . $applicant->country : '—' }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Department') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->department?->name ?? '—' }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Designation') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->designation?->title ?? '—' }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Expected Salary') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->expected_salary ? $applicant->currency . ' ' . number_format($applicant->expected_salary, 0) : '—' }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Available From') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->available_from ? $applicant->available_from->format('M d, Y') : '—' }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Source') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->source ?? '—' }}</p>
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Applied') }}</flux:text>
                                <p class="mt-1.5 text-zinc-700 dark:text-zinc-300">{{ $applicant->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        @if ($applicant->cover_letter)
                            <flux:separator class="my-5" />
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Cover Letter') }}</flux:text>
                                <p class="mt-1.5 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed whitespace-pre-wrap">{{ $applicant->cover_letter }}</p>
                            </div>
                        @endif

                        @if ($applicant->notes)
                            <flux:separator class="my-5" />
                            <div>
                                <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Notes') }}</flux:text>
                                <p class="mt-1.5 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed whitespace-pre-wrap">{{ $applicant->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Hire / Reject Actions -->
                @if (!$applicant->isHired() && !$applicant->isRejected() && auth()->user()->isSeniorMember())
                    <div class="hrms-card">
                        <div class="hrms-card-body">
                            <flux:heading size="md" class="mb-4 text-zinc-900 dark:text-white">{{ __('Actions') }}</flux:heading>

                            <!-- Hire Form -->
                            <form method="POST" action="{{ route('applicants.hire', $applicant) }}" class="flex flex-col gap-4 mb-4">
                                @csrf
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300">{{ __('Hire Applicant') }}</flux:heading>
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:input name="employee_id" :label="__('Employee ID')" :value="old('employee_id')" placeholder="EMP-001" />
                                    <flux:input name="job_title" :label="__('Job Title')" :value="old('job_title', $applicant->designation?->title)" />
                                </div>
                                <flux:input name="position" :label="__('Position')" :value="old('position', $applicant->designation?->title)" required />
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:select name="contract_type" :label="__('Contract Type')" required>
                                        <option value="full_time" @selected(old('contract_type') == 'full_time')>{{ __('Full Time') }}</option>
                                        <option value="part_time" @selected(old('contract_type') == 'part_time')>{{ __('Part Time') }}</option>
                                        <option value="contract" @selected(old('contract_type') == 'contract')>{{ __('Contract') }}</option>
                                        <option value="internship" @selected(old('contract_type') == 'internship')>{{ __('Internship') }}</option>
                                    </flux:select>
                                    <flux:select name="role" :label="__('Role')" required>
                                        <option value="employee" @selected(old('role') == 'employee')>{{ __('Employee') }}</option>
                                        <option value="hr_executive" @selected(old('role') == 'hr_executive')>{{ __('HR Executive') }}</option>
                                        <option value="department_head" @selected(old('role') == 'department_head')>{{ __('Department Head') }}</option>
                                    </flux:select>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:input name="start_date" :label="__('Start Date')" type="date" :value="old('start_date')" required />
                                    <flux:input name="end_date" :label="__('End Date')" type="date" :value="old('end_date')" />
                                </div>
                                <flux:input name="salary" :label="__('Salary')" type="number" step="0.01" :value="old('salary', $applicant->expected_salary)" placeholder="0.00" />
                                <flux:button type="submit" variant="primary" icon="check" class="w-full">{{ __('Hire as Employee') }}</flux:button>
                            </form>

                            <flux:separator class="my-4" />

                            <!-- Reject Form -->
                            <form method="POST" action="{{ route('applicants.reject', $applicant) }}">
                                @csrf
                                <flux:button type="submit" variant="danger" icon="x-mark" class="w-full" onclick="return confirm('{{ __('Are you sure you want to reject this applicant?') }}')">{{ __('Reject Applicant') }}</flux:button>
                            </form>
                        </div>
                    </div>
                @elseif ($applicant->isHired())
                    <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 p-4 flex items-center gap-3">
                        <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                        <div>
                            <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ __('Hired') }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ $applicant->reviewed_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                @elseif ($applicant->isRejected())
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 flex items-center gap-3">
                        <flux:icon name="x-mark" class="size-5 text-red-600 dark:text-red-400 shrink-0" />
                        <div>
                            <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ __('Rejected') }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400">{{ $applicant->reviewed_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Review Info -->
                @if ($applicant->reviewer)
                    <div class="hrms-card">
                        <div class="hrms-card-body">
                            <flux:text class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Reviewed By') }}</flux:text>
                            <div class="flex items-center gap-2 mt-1.5">
                                <flux:avatar :name="$applicant->reviewer->name" size="xs" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $applicant->reviewer->name }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
