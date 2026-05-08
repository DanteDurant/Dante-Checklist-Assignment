<x-layouts.base :title="$title ?? 'Admin'">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">Admin</p>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $heading ?? 'Dashboard' }}</h1>
        </div>
        <div class="flex items-center gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>

    {{ $slot }}
</x-layouts.base>

