@props([
    'template' => null,
    'statuses' => [],
    'submitLabel' => 'Save',
])

@include('admin.templates._form', [
    'template' => $template,
    'statuses' => $statuses,
    'submitLabel' => $submitLabel,
])

