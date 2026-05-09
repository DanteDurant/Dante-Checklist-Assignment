@props([
    'title',
    /** Collapsed by default for progressive disclosure */
    'open' => false,
])

<details
    {{ $attributes->merge([
        'class' => 'group rounded-xl border border-ui-border bg-ui-surface shadow-sm shadow-black/[0.04] dark:shadow-black/30',
    ]) }}
    @if ($open) open @endif
>
    <summary
        class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-ui-fg outline-none transition hover:bg-ui-muted/50 dark:hover:bg-ui-elevated/60 marker:content-none [&::-webkit-details-marker]:hidden rounded-xl focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas"
    >
        <span class="min-w-0">{{ $title }}</span>
        <svg
            class="h-4 w-4 shrink-0 text-ui-fg-subtle transition-transform duration-200 group-open:rotate-180"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
        >
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </summary>
    <div class="border-t border-ui-border px-4 py-4">
        {{ $slot }}
    </div>
</details>
