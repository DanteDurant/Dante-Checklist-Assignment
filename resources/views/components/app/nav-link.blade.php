@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}"
   {{ $attributes->class([
        'rounded-md px-3 py-2 text-sm font-medium transition',
        'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-slate-100' => !$active,
   ]) }}
>
    {{ $slot }}
</a>

