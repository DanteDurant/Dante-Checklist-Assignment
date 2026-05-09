@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950']) }}>
    @if ($title)
        <div class="mb-4">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>

