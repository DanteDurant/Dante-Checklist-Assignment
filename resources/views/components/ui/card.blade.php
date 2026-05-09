@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-ui-border bg-ui-surface p-5 shadow-sm shadow-black/[0.04] dark:shadow-black/30']) }}>
    @if ($title)
        <div class="mb-4">
            <h2 class="text-base font-semibold text-ui-fg">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1.5 text-sm leading-relaxed text-ui-fg-muted">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
