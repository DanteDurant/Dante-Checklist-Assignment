@props([
    'type' => 'text',
])

<input type="{{ $type }}"
       {{ $attributes->merge(['class' => 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 disabled:bg-slate-100 disabled:text-slate-500']) }} />

