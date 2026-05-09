@props([
    'label',
    'name',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label class="block text-sm font-medium text-ui-fg-muted" for="{{ $name }}">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="text-xs leading-relaxed text-ui-fg-subtle">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-sm font-medium text-rose-700 dark:text-rose-300" role="alert">{{ $message }}</p>
    @enderror
</div>
