@props([
    'status',
])

@php
    $label = null;
    $classes = 'bg-slate-50 text-slate-800 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800';

    if ($status instanceof \App\Enums\ChecklistInstanceStatus || $status instanceof \App\Enums\ChecklistTemplateStatus) {
        $label = $status->label();
        $classes = $status->badgeClasses();
    } else {
        $raw = is_string($status) ? $status : (string) $status;
        $label = str_replace('_', ' ', ucfirst($raw));
    }
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset '.$classes]) }}>
    {{ $label }}
</span>

