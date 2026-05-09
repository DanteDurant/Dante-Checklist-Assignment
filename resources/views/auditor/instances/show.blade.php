@php
    $isEditable = in_array($instance->status->value, ['draft', 'in_progress'], true);
@endphp

<x-layouts.auditor :title="'Checklist: '.($instance->template?->name ?? $instance->id)" :heading="$instance->template?->name ?? 'Checklist Instance'">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm leading-relaxed text-ui-fg-muted">
            <span class="font-semibold text-ui-fg">Status:</span> <x-ui.status-badge :status="$instance->status" />
            <span class="mx-2 text-ui-fg-subtle" aria-hidden="true">·</span>
            <span class="font-semibold text-ui-fg">Version:</span> <span class="tabular-nums">{{ $instance->current_version }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button :href="route('auditor.dashboard')" variant="secondary">Back</x-ui.button>
        </div>
    </div>

    @can('exportPdf', $instance)
        <div class="mt-4 max-w-2xl">
            <x-ui.disclosure title="Export checklist (PDF)">
                <x-export.pdf-options
                    variant="flush"
                    :heading="false"
                    :action="route('auditor.instances.export_pdf', $instance)"
                    submit-label="Download checklist PDF"
                    :show-sections-hint="true"
                />
            </x-ui.disclosure>
        </div>
    @endcan

    <div class="mt-6">
        <x-ui.card title="Checklist questions" description="Answer in order. Save draft anytime.">
            <form method="POST" action="{{ route('auditor.instances.draft', $instance) }}" class="space-y-6">
                @csrf

                <div class="space-y-6">
                    @foreach ($questions as $q)
                        @php
                            $existing = $answers->get($q->id);
                            $stored = $existing?->value ?? [];

                            $answerKey = "answers.{$q->id}";

                            $type = $q->type?->value;

                            $oldValue = old($answerKey);
                            $value = $oldValue !== null
                                ? $oldValue
                                : match ($type) {
                                    'boolean' => (bool) ($stored['boolean'] ?? false),
                                    'number' => $stored['number'] ?? null,
                                    'date' => $stored['date'] ?? null,
                                    'datetime' => $stored['datetime'] ?? null,
                                    'select', 'single_select', 'radio' => $stored['choice'] ?? null,
                                    'checkbox', 'multi_select' => $stored['choices'] ?? [],
                                    'textarea' => $stored['text'] ?? null,
                                    'text', 'email', 'phone', 'url' => $stored['text'] ?? null,
                                    default => $stored,
                                };
                        @endphp

                        <div class="rounded-lg border border-ui-border bg-ui-fill p-4 shadow-sm shadow-black/[0.03] dark:shadow-black/20">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold leading-snug text-ui-fg">
                                        {{ $q->sort_order }}. {{ $q->label }}
                                        @if ($q->is_required)
                                            <span class="ml-1 font-bold text-rose-600 dark:text-rose-400" title="Required" aria-hidden="true">*</span>
                                            <span class="sr-only">(required)</span>
                                        @endif
                                    </p>
                                    @if ($q->help_text)
                                        <p class="mt-1 text-sm leading-relaxed text-ui-fg-muted">{{ $q->help_text }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 rounded-full bg-ui-muted px-2 py-1 text-xs font-semibold text-ui-fg-muted ring-1 ring-inset ring-ui-border">
                                    {{ $q->type->value }}
                                </div>
                            </div>

                            <div class="mt-3">
                                <x-auditor.answer-input
                                    :question="$q"
                                    :name="'answers['.$q->id.']'"
                                    :value="$value"
                                    :disabled="!$isEditable"
                                />

                                @error($answerKey)
                                <p class="mt-2 text-sm font-medium text-rose-700 dark:text-rose-300" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('status')
                <p class="text-sm font-medium text-rose-700 dark:text-rose-300" role="alert">{{ $message }}</p>
                @enderror

                <div class="flex flex-wrap gap-2 pt-2">
                    @if ($isEditable)
                        <x-ui.button type="submit" variant="secondary" data-loading-text="Saving...">Save draft</x-ui.button>

                        <button type="submit"
                                formaction="{{ route('auditor.instances.submit', $instance) }}"
                                data-loading-text="Submitting..."
                                data-confirm="Submit this checklist? You won’t be able to edit answers after submission."
                                class="inline-flex items-center justify-center rounded-md bg-ui-accent px-3 py-2 text-sm font-semibold text-ui-accent-fg shadow-sm transition hover:bg-ui-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas disabled:pointer-events-none disabled:opacity-45">
                            Submit checklist
                        </button>
                    @else
                        <span class="text-sm text-ui-fg-muted">This checklist is completed and locked.</span>
                    @endif
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.auditor>
