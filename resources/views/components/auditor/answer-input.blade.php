@props([
    'question',
    'name',
    'value' => null,
    'disabled' => false,
])

@php
    /** @var \App\Models\ChecklistQuestion $question */
    $type = $question->type?->value;

    $rawOptions = $question->options ?? [];
    $options = [];

    foreach ($rawOptions as $opt) {
        if (is_array($opt)) {
            $val = (string) ($opt['value'] ?? ($opt['id'] ?? ''));
            $label = (string) ($opt['label'] ?? ($opt['name'] ?? $val));
            if ($val !== '') {
                $options[] = ['value' => $val, 'label' => $label];
            }
        } else {
            $val = (string) $opt;
            if ($val !== '') {
                $options[] = ['value' => $val, 'label' => $val];
            }
        }
    }

    $isArrayInput = in_array($type, ['checkbox', 'multi_select'], true);
    $selectedValues = $isArrayInput ? (is_array($value) ? $value : []) : null;
@endphp

@if ($type === 'textarea')
    <x-ui.textarea :name="$name" rows="4" :disabled="$disabled">{{ (string) ($value ?? '') }}</x-ui.textarea>
@elseif (in_array($type, ['text', 'email', 'phone', 'url'], true))
    @php
        $htmlType = match ($type) {
            'email' => 'email',
            'phone' => 'tel',
            'url' => 'url',
            default => 'text',
        };
    @endphp
    <x-ui.input :name="$name" :type="$htmlType" :value="(string) ($value ?? '')" :disabled="$disabled" />
@elseif ($type === 'number')
    <x-ui.input :name="$name" type="number" step="any" :value="($value === null || $value === '') ? '' : (string) $value" :disabled="$disabled" />
@elseif ($type === 'date')
    <x-ui.input :name="$name" type="date" :value="(string) ($value ?? '')" :disabled="$disabled" />
@elseif ($type === 'datetime')
    <x-ui.input :name="$name" type="datetime-local" :value="(string) ($value ?? '')" :disabled="$disabled" />
@elseif ($type === 'boolean')
    <div class="flex items-center gap-2">
        <input type="hidden" name="{{ $name }}" value="0" />
        <input id="q_{{ $question->id }}" name="{{ $name }}" type="checkbox" value="1"
               @checked((bool) $value)
               @disabled($disabled)
               class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
        <label for="q_{{ $question->id }}" class="text-sm text-slate-700">Yes</label>
    </div>
@elseif (in_array($type, ['select', 'single_select'], true))
    <x-ui.select :name="$name" :disabled="$disabled">
        <option value="">Select…</option>
        @foreach ($options as $opt)
            <option value="{{ $opt['value'] }}" @selected((string) $value === (string) $opt['value'])>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </x-ui.select>
@elseif ($type === 'radio')
    <div class="space-y-2">
        @foreach ($options as $opt)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="radio" name="{{ $name }}" value="{{ $opt['value'] }}"
                       @checked((string) $value === (string) $opt['value'])
                       @disabled($disabled)
                       class="h-4 w-4 border-slate-300 text-slate-900 focus:ring-slate-900" />
                <span>{{ $opt['label'] }}</span>
            </label>
        @endforeach
    </div>
@elseif (in_array($type, ['checkbox', 'multi_select'], true))
    <div class="space-y-2">
        @foreach ($options as $opt)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="{{ $name }}[]" value="{{ $opt['value'] }}"
                       @checked(in_array((string) $opt['value'], array_map('strval', $selectedValues ?? []), true))
                       @disabled($disabled)
                       class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
                <span>{{ $opt['label'] }}</span>
            </label>
        @endforeach
    </div>
@else
    <x-ui.textarea :name="$name" rows="3" :disabled="$disabled">{{ is_string($value) ? $value : json_encode($value) }}</x-ui.textarea>
    <p class="mt-1 text-xs text-slate-500">
        This answer type is not fully implemented in Blade yet.
    </p>
@endif

