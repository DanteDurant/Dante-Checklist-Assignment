@props([
    'variant' => 'primary', // primary | secondary | danger
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    $variants = [
        'primary' => 'bg-slate-900 text-white hover:bg-slate-800 focus:ring-slate-900 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white dark:focus:ring-slate-200 dark:focus:ring-offset-slate-950',
        'secondary' => 'bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:ring-slate-400 dark:bg-slate-950 dark:text-slate-100 dark:ring-slate-700 dark:hover:bg-slate-900 dark:focus:ring-slate-600 dark:focus:ring-offset-slate-950',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-500 focus:ring-rose-600 dark:bg-rose-500 dark:hover:bg-rose-400 dark:focus:ring-rose-400 dark:focus:ring-offset-slate-950',
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

