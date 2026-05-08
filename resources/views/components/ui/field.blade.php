@props([
    'label',
    'name',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <label class="block text-sm font-medium text-slate-700" for="{{ $name }}">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($name)
    <p class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>

