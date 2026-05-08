<x-layouts.admin :title="$template->name" :heading="$template->name">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-slate-600">
            <span class="font-medium text-slate-900">Status:</span> {{ $template->status->value }}
            <span class="mx-2 text-slate-300">•</span>
            <span class="font-medium text-slate-900">Questions:</span> {{ $template->questions->count() }}
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button :href="route('admin.templates.edit', $template)" variant="secondary">Edit</x-ui.button>
            <x-ui.button :href="route('admin.templates.index')" variant="secondary">Back</x-ui.button>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card title="Template details">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Title</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $template->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public ID</dt>
                        <dd class="mt-1 font-mono text-xs text-slate-700">{{ $template->public_id }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $template->description ?: '—' }}</dd>
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
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $q->label }}
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                            <span class="rounded-md bg-slate-50 px-2 py-1 ring-1 ring-inset ring-slate-200">
                                                Sort: {{ $q->sort_order }}
                                            </span>
                                            <span class="rounded-md bg-slate-50 px-2 py-1 ring-1 ring-inset ring-slate-200">
                                                Type: {{ $q->type->value }}
                                            </span>
                                            <span class="rounded-md bg-slate-50 px-2 py-1 ring-1 ring-inset ring-slate-200">
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
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $q->sort_order }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $q->label }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $q->type->value }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $q->is_required ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <form method="POST" action="{{ route('admin.templates.questions.destroy', [$template, $q]) }}"
                                              data-confirm="Delete this question?">
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

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="question_text">Question text</label>
                        <textarea id="question_text" name="question_text" rows="4" required
                                  class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">{{ old('question_text') }}</textarea>
                        @error('question_text')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="answer_type">Answer type</label>
                        <select id="answer_type" name="answer_type" required
                                class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                            @foreach (\App\Enums\ChecklistQuestionType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('answer_type') === $type->value)>
                                    {{ str_replace('_', ' ', ucfirst($type->value)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('answer_type')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="required" name="required" type="checkbox" value="1"
                               class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                               @checked(old('required')) />
                        <label class="text-sm text-slate-700" for="required">Required</label>
                    </div>
                    @error('required')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="sort_order">Sort order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" max="1000000" required
                               value="{{ old('sort_order', 0) }}"
                               class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"/>
                        @error('sort_order')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <x-ui.button type="submit" data-loading-text="Adding...">Add question</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>

