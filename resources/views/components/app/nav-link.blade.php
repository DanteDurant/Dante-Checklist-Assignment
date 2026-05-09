@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}"
   {{ $attributes->class([
        'rounded-md px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-surface',
        'bg-ui-accent text-ui-accent-fg shadow-sm hover:bg-ui-accent-hover' => $active,
        'text-ui-fg-muted hover:bg-ui-muted hover:text-ui-fg active:bg-ui-elevated' => ! $active,
   ]) }}
>
    {{ $slot }}
</a>
