@props([
    'title' => 'No data',
    'message' => null,
])

<div class="rounded-lg border border-dashed border-ui-border bg-ui-muted/50 px-4 py-8 text-center">
    <p class="text-sm font-semibold text-ui-fg">{{ $title }}</p>
    @if ($message)
        <p class="mt-1.5 text-sm leading-relaxed text-ui-fg-muted">{{ $message }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-4 flex justify-center">
            {{ $slot }}
        </div>
    @endif
</div>
