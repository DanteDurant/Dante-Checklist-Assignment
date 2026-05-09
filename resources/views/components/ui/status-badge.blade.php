@props([
    'status',
])

@php
    $label = null;
    $classes = 'bg-ui-muted text-ui-fg ring-ui-border';

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
