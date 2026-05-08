@props([
    'type' => 'success', // success | error | info
    'message' => null,
])

@php
    $styles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'error' => 'border-rose-200 bg-rose-50 text-rose-900',
        'info' => 'border-slate-200 bg-white text-slate-900',
    ];
@endphp

@if ($message)
    <div {{ $attributes->merge(['class' => 'mb-6 rounded-lg border px-4 py-3 text-sm '.$styles[$type]]) }}>
        {{ $message }}
    </div>
@endif

