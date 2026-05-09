@props([
    'headers' => [],
])

<div class="overflow-hidden rounded-xl border border-ui-border bg-ui-surface shadow-sm shadow-black/[0.04] dark:shadow-black/30">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-ui-border">
        @if (!empty($headers))
            <thead class="bg-ui-muted">
            <tr>
                @foreach ($headers as $header)
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ui-fg-muted">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-ui-border">
        {{ $slot }}
        </tbody>
        </table>
    </div>
</div>
