@props([
    'title' => 'No data',
    'message' => null,
])

<div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center dark:border-slate-800 dark:bg-slate-900/30">
    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</p>
    @if ($message)
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $message }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-4 flex justify-center">
            {{ $slot }}
        </div>
    @endif
</div>

