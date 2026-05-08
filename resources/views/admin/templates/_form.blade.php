@props([
    'template' => null,
    'statuses' => [],
    'submitLabel' => 'Save',
])

<div class="space-y-5">
    <x-ui.field label="Title" name="title">
        <x-ui.input id="title" name="title" type="text" required value="{{ old('title', $template?->name) }}" />
    </x-ui.field>

    <x-ui.field label="Description" name="description">
        <x-ui.textarea id="description" name="description" rows="4">{{ old('description', $template?->description) }}</x-ui.textarea>
    </x-ui.field>

    <x-ui.field label="Status" name="status">
        <x-ui.select id="status" name="status" required>
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}" @selected(old('status', $template?->status?->value) === $s->value)>
                    {{ ucfirst($s->value) }}
                </option>
            @endforeach
        </x-ui.select>
    </x-ui.field>

    <div class="pt-2">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
        <x-ui.button :href="route('admin.templates.index')" variant="secondary">Cancel</x-ui.button>
    </div>
</div>

