@php
    $isEditable = in_array($instance->status->value, ['draft', 'in_progress'], true);
@endphp

<x-layouts.auditor :title="'Checklist: '.($instance->template?->name ?? $instance->id)" :heading="$instance->template?->name ?? 'Checklist Instance'">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-slate-600">
            <span class="font-medium text-slate-900">Status:</span> {{ $instance->status->value }}
            <span class="mx-2 text-slate-300">•</span>
            <span class="font-medium text-slate-900">Version:</span> {{ $instance->current_version }}
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button :href="route('auditor.dashboard')" variant="secondary">Back</x-ui.button>
        </div>
    </div>

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

                            $textValue = old($answerKey, $stored['text'] ?? '');
                            $numberValue = old($answerKey, $stored['number'] ?? '');
                            $boolValue = old($answerKey, $stored['boolean'] ?? false);
                        @endphp

                        <div class="rounded-lg border border-slate-200 bg-white p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $q->sort_order }}. {{ $q->label }}
                                        @if ($q->is_required)
                                            <span class="ml-1 text-rose-600">*</span>
                                        @endif
                                    </p>
                                    @if ($q->help_text)
                                        <p class="mt-1 text-sm text-slate-500">{{ $q->help_text }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 rounded-full bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">
                                    {{ $q->type->value }}
                                </div>
                            </div>

                            <div class="mt-3">
                                @if ($q->type->value === 'text')
                                    <x-ui.input name="answers[{{ $q->id }}]" type="text" value="{{ $textValue }}" :disabled="!$isEditable" />
                                @elseif ($q->type->value === 'number')
                                    <x-ui.input name="answers[{{ $q->id }}]" type="number" step="any" value="{{ $numberValue }}" :disabled="!$isEditable" />
                                @elseif ($q->type->value === 'boolean')
                                    <div class="flex items-center gap-2">
                                        <input type="hidden" name="answers[{{ $q->id }}]" value="0" />
                                        <input id="q_{{ $q->id }}" name="answers[{{ $q->id }}]" type="checkbox" value="1"
                                               @checked((bool) $boolValue)
                                               @disabled(!$isEditable)
                                               class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
                                        <label for="q_{{ $q->id }}" class="text-sm text-slate-700">Yes</label>
                                    </div>
                                @else
                                    <x-ui.textarea name="answers[{{ $q->id }}]" rows="3" :disabled="!$isEditable">{{ old($answerKey, json_encode($stored)) }}</x-ui.textarea>
                                    <p class="mt-1 text-xs text-slate-500">
                                        This answer type is not fully implemented in Blade yet.
                                    </p>
                                @endif

                                @error($answerKey)
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('status')
                <p class="text-sm text-rose-600">{{ $message }}</p>
                @enderror

                <div class="flex flex-wrap gap-2 pt-2">
                    @if ($isEditable)
                        <x-ui.button type="submit" variant="secondary" data-loading-text="Saving...">Save draft</x-ui.button>

                        <button type="submit"
                                formaction="{{ route('auditor.instances.submit', $instance) }}"
                                data-loading-text="Submitting..."
                                class="inline-flex items-center justify-center rounded-md bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                            Submit checklist
                        </button>
                    @else
                        <span class="text-sm text-slate-600">This checklist is completed and locked.</span>
                    @endif
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.auditor>

