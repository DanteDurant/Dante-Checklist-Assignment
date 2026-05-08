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
                          onsubmit="return confirm('Delete this template? This will cascade-delete its questions.');">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger">Delete template</x-ui.button>
                    </form>
                </div>
            </x-ui.card>

            <div class="mt-6">
                <x-ui.card title="Questions" description="Ordered by sort order.">
                    <x-ui.table :headers="['Sort', 'Question', 'Type', 'Required', 'Actions']">
                        @forelse ($template->questions as $q)
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $q->sort_order }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $q->label }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $q->type->value }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $q->is_required ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <form method="POST" action="{{ route('admin.templates.questions.destroy', [$template, $q]) }}"
                                          onsubmit="return confirm('Delete this question?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger">Delete</x-ui.button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-sm text-slate-500">No questions yet.</td>
                            </tr>
                        @endforelse
                    </x-ui.table>
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
                        <x-ui.button type="submit">Add question</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>

