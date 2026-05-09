@props([
    'type' => 'success', // success | error | info
    'message' => null,
])

@php
    $styles = [
        'success' => 'border-emerald-300/80 bg-emerald-50 text-emerald-950 dark:border-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-50',
        'error' => 'border-rose-300/80 bg-rose-50 text-rose-950 dark:border-rose-800 dark:bg-rose-950/80 dark:text-rose-50',
        'info' => 'border-ui-border bg-ui-muted text-ui-fg',
    ];

    $role = match ($type) {
        'error' => 'alert',
        default => 'status',
    };
@endphp

@if ($message)
    <div {{ $attributes->merge(['role' => $role, 'class' => 'mb-6 rounded-lg border px-4 py-3 text-sm font-medium leading-relaxed '.$styles[$type]]) }}>
        {{ $message }}
    </div>
@endif
