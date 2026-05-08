@props([
    'headers' => [],
])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200">
        @if (!empty($headers))
            <thead class="bg-slate-50">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-slate-200">
        {{ $slot }}
        </tbody>
    </table>
</div>

