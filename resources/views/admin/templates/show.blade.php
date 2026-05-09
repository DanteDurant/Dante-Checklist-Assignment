<x-layouts.admin :title="$template->name" :heading="$template->name">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="text-sm leading-relaxed text-ui-fg-muted">
            <span class="font-semibold text-ui-fg">Status:</span> <x-ui.status-badge :status="$template->status" />
            <span class="mx-2 text-ui-fg-subtle" aria-hidden="true">·</span>
            <span class="font-semibold text-ui-fg">Questions:</span> {{ $template->questions->count() }}
        </div>
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:max-w-md sm:items-stretch">
            <div class="flex flex-wrap gap-2 sm:justify-end">
                <x-ui.button :href="route('admin.templates.edit', $template)" variant="secondary">Edit</x-ui.button>
                <x-ui.button :href="route('admin.templates.index')" variant="secondary">Back</x-ui.button>
            </div>
            <x-ui.disclosure title="Export template (PDF)">
                <x-export.pdf-options
                    variant="flush"
                    :heading="false"
                    :action="route('admin.templates.export_pdf', $template)"
                    submit-label="Download template PDF"
                />
            </x-ui.disclosure>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card title="Template details">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Title</dt>
                        <dd class="mt-1 text-sm text-ui-fg">{{ $template->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Public ID</dt>
                        <dd class="mt-1 font-mono text-xs text-ui-fg-muted">{{ $template->public_id }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Description</dt>
                        <dd class="mt-1 text-sm leading-relaxed text-ui-fg-muted">{{ $template->description ?: '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-5">
                    <form method="POST" action="{{ route('admin.templates.destroy', $template) }}"
                          data-confirm="Delete this template? This will cascade-delete its questions.">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" data-loading-text="Deleting...">Delete template</x-ui.button>
                    </form>
                </div>
            </x-ui.card>

            <div class="mt-6">
                <x-ui.card title="Questions" description="Ordered by sort order.">
                    <div class="space-y-3 sm:hidden">
                        @forelse ($template->questions as $q)
                            <x-ui.card>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-ui-fg">
                                            {{ $q->label }}
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                            <span class="rounded-md bg-ui-muted px-2 py-1 font-medium ring-1 ring-inset ring-ui-border text-ui-fg-muted">
                                                Sort: {{ $q->sort_order }}
                                            </span>
                                            <span class="rounded-md bg-ui-muted px-2 py-1 font-medium ring-1 ring-inset ring-ui-border text-ui-fg-muted">
                                                Type: {{ $q->type->value }}
                                            </span>
                                            <span class="rounded-md bg-ui-muted px-2 py-1 font-medium ring-1 ring-inset ring-ui-border text-ui-fg-muted">
                                                Required: {{ $q->is_required ? 'Yes' : 'No' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <form method="POST" action="{{ route('admin.templates.questions.destroy', [$template, $q]) }}"
                                              data-confirm="Delete this question?">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger" data-loading-text="Deleting...">Delete</x-ui.button>
                                        </form>
                                    </div>
                                </div>
                            </x-ui.card>
                        @empty
                            <x-ui.empty-state title="No questions yet" message="Add your first question using the form on the right." />
                        @endforelse
                    </div>

                    <div class="hidden sm:block">
                        <x-ui.table :headers="['Sort', 'Question', 'Type', 'Required', 'Actions']">
                            @forelse ($template->questions as $q)
                                <tr>
                                    <td class="px-4 py-3 text-sm tabular-nums text-ui-fg-muted">{{ $q->sort_order }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-ui-fg">{{ $q->label }}</td>
                                    <td class="px-4 py-3 text-sm text-ui-fg-muted">{{ $q->type->value }}</td>
                                    <td class="px-4 py-3 text-sm text-ui-fg-muted">{{ $q->is_required ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <form method="POST" action="{{ route('admin.templates.questions.destroy', [$template, $q]) }}"
                                              data-confirm="Delete this question?"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger" data-loading-text="Deleting...">Delete</x-ui.button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6">
                                        <x-ui.empty-state title="No questions yet" message="Add your first question using the form on the right." />
                                    </td>
                                </tr>
                            @endforelse
                        </x-ui.table>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <div>
            <x-ui.card title="Add question" description="Simple form-based creation (no JS framework).">
                <form method="POST" action="{{ route('admin.templates.questions.store', $template) }}" class="space-y-4">
                    @csrf

                    <x-ui.field label="Question text" name="question_text">
                        <x-ui.textarea id="question_text" name="question_text" rows="4" required>{{ old('question_text') }}</x-ui.textarea>
                    </x-ui.field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Answer type" name="answer_type">
                            <x-ui.select id="answer_type" name="answer_type" required>
                                @foreach (\App\Enums\ChecklistQuestionType::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(old('answer_type') === $type->value)>
                                        {{ str_replace('_', ' ', ucfirst($type->value)) }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <div class="space-y-1.5">
                            <span class="block text-sm font-medium text-ui-fg-muted" id="required-label">Required</span>
                            <div class="mt-2 flex items-center gap-2">
                                <input id="required" name="required" type="checkbox" value="1"
                                       aria-labelledby="required-label"
                                       class="h-4 w-4 rounded border-ui-fill-border text-ui-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas"
                                       @checked(old('required')) />
                                <label for="required" class="text-sm font-medium text-ui-fg-muted">Yes</label>
                            </div>
                            @error('required')
                                <p class="text-sm font-medium text-rose-700 dark:text-rose-300" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <x-ui.field label="Options (for select/radio/checkbox)" name="options_text" hint="Only used for choice-based question types.">
                        <x-ui.textarea id="options_text" name="options_text" rows="4" placeholder="One option per line">{{ old('options_text') }}</x-ui.textarea>
                    </x-ui.field>

                    <x-ui.field label="Sort order" name="sort_order">
                        <x-ui.input id="sort_order" name="sort_order" type="number" min="0" max="1000000" required
                                    value="{{ old('sort_order', 0) }}" />
                    </x-ui.field>

                    <div class="pt-2">
                        <x-ui.button type="submit" data-loading-text="Adding...">Add question</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
