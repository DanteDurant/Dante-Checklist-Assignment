@props([
    'variant' => 'primary', // primary | secondary | danger
    'href' => null,
    'type' => 'button',
    'size' => 'md', // sm | md
])

@php
    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs font-semibold',
        'md' => 'px-3 py-2 text-sm font-semibold',
    ];

    $base = 'inline-flex items-center justify-center rounded-md transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas disabled:pointer-events-none disabled:opacity-45 '.($sizes[$size] ?? $sizes['md']);

    $variants = [
        'primary' => 'bg-ui-accent text-ui-accent-fg shadow-sm hover:bg-ui-accent-hover focus-visible:ring-ui-ring active:opacity-90',
        'secondary' => 'bg-ui-surface text-ui-fg ring-1 ring-inset ring-ui-border shadow-sm hover:bg-ui-muted focus-visible:ring-ui-ring active:bg-ui-elevated',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-500 focus-visible:ring-rose-400 active:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
