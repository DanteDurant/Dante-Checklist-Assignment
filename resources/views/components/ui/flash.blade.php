@props([
    'type' => 'success', // success | error | info
    'message' => null,
])

@php
    $styles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100',
        'error' => 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-100',
        'info' => 'border-slate-200 bg-white text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100',
    ];
@endphp

@if ($message)
    <div {{ $attributes->merge(['class' => 'mb-6 rounded-lg border px-4 py-3 text-sm '.$styles[$type]]) }}>
        {{ $message }}
    </div>
@endif

