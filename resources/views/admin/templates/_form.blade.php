@props([
    'template' => null,
    'statuses' => [],
    'submitLabel' => 'Save',
])

<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-slate-700" for="title">Title</label>
        <input id="title" name="title" type="text" required
               value="{{ old('title', $template?->name) }}"
               class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"/>
        @error('title')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="description">Description</label>
        <textarea id="description" name="description" rows="4"
                  class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">{{ old('description', $template?->description) }}</textarea>
        @error('description')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="status">Status</label>
        <select id="status" name="status" required
                class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}" @selected(old('status', $template?->status?->value) === $s->value)>
                    {{ ucfirst($s->value) }}
                </option>
            @endforeach
        </select>
        @error('status')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="pt-2">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
        <x-ui.button :href="route('admin.templates.index')" variant="secondary">Cancel</x-ui.button>
    </div>
</div>

