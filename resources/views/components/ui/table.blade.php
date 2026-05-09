@props([
    'headers' => [],
])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
        @if (!empty($headers))
            <thead class="bg-slate-50 dark:bg-slate-900">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        {{ $slot }}
        </tbody>
        </table>
    </div>
</div>

