<x-layouts.base :title="$title ?? 'Admin'">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Admin</p>
            <h1 class="mt-1 text-2xl font-semibold text-ui-fg">{{ $heading ?? 'Dashboard' }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>

    {{ $slot }}
</x-layouts.base>
