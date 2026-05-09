@props([
    'action',
    'submitLabel' => 'Download PDF',
    'method' => 'GET',
    /** @var array<string, scalar|null> $hiddenFields */
    'hiddenFields' => [],
    'showSectionsHint' => false,
    'showSnapshotDates' => false,
    /** panel = standalone card; embedded = dashed inset; flush = no chrome (inside disclosure/cards) */
    'variant' => 'panel',
    /** Show top title line (disable when parent provides context) */
    'heading' => true,
])

@php
    $shellClass = match ($variant) {
        'embedded' => 'rounded-lg border border-dashed border-ui-border/90 bg-ui-fill/50 p-3 sm:p-4 dark:border-ui-border dark:bg-ui-fill/30',
        'flush' => 'space-y-3',
        default => 'rounded-xl border border-ui-border bg-ui-surface p-4 shadow-sm shadow-black/[0.04] dark:shadow-black/30',
    };
@endphp

<form
    method="{{ $method }}"
    action="{{ $action }}"
    data-pdf-export="true"
    {{ $attributes->merge(['class' => $shellClass]) }}
>
    @if ($heading)
        <div class="mb-3">
            <div class="text-sm font-semibold text-ui-fg">Export to PDF</div>
            <p class="mt-1 text-xs leading-relaxed text-ui-fg-muted">
                Density controls file detail; filenames include date and level for audit trails.
            </p>
        </div>
    @endif

    @if ($showSectionsHint)
        <p class="mb-3 text-xs text-ui-fg-subtle">
            API: optional <code class="rounded bg-ui-muted px-1 py-0.5 font-mono text-[10px] text-ui-fg-muted dark:bg-ui-elevated">sections=…</code>
        </p>
    @endif

    @if ($showSnapshotDates)
        <div class="mb-4 grid max-w-lg gap-3 sm:grid-cols-2">
            <x-ui.field label="From" name="date_from">
                <x-ui.input id="pdf_date_from" name="date_from" type="date" value="{{ request('date_from') }}" />
            </x-ui.field>
            <x-ui.field label="To" name="date_to">
                <x-ui.input id="pdf_date_to" name="date_to" type="date" value="{{ request('date_to') }}" />
            </x-ui.field>
        </div>
    @endif

    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between lg:gap-4">
        <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-end sm:gap-3">
            @foreach ($hiddenFields as $name => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
                @endif
            @endforeach

            <div class="w-full sm:max-w-xs">
                <label class="block text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle" for="pdf_detail_{{ md5($action) }}">
                    Detail level
                </label>
                <select
                    id="pdf_detail_{{ md5($action) }}"
                    name="detail"
                    class="mt-1.5 w-full rounded-md border border-ui-fill-border bg-ui-fill px-3 py-2 text-sm text-ui-fg shadow-sm focus:border-ui-ring focus:outline-none focus:ring-2 focus:ring-ui-ring/40 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-ui-muted disabled:text-ui-fg-subtle dark:bg-ui-fill"
                >
                    <option value="summary">Summary</option>
                    <option value="standard" selected>Standard</option>
                    <option value="detailed">Detailed audit</option>
                    <option value="executive">Executive</option>
                </select>
            </div>
        </div>

        <div class="flex shrink-0 lg:justify-end">
            <x-ui.button type="submit" class="w-full min-w-[9rem] sm:w-auto" data-loading-text="Generating PDF…">
                {{ $submitLabel }}
            </x-ui.button>
        </div>
    </div>
</form>
