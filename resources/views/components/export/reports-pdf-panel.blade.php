@props([
    /** @var array<string, scalar|null> $hiddenFields */
    'hiddenFields' => [],
])

<div class="rounded-xl border border-ui-border bg-ui-muted/40 p-4 dark:bg-ui-elevated/35">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-ui-fg">PDF exports</h3>
            <p class="mt-0.5 text-xs leading-relaxed text-ui-fg-muted">
                Applies the filters above (pagination excluded). Choose density, then download.
            </p>
        </div>
    </div>

    <div class="mt-4 space-y-4">
        <div>
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-ui-fg-subtle">
                Completion register
            </p>
            <x-export.pdf-options
                variant="embedded"
                :heading="false"
                :action="route('admin.reports.checklist_instances_pdf')"
                submit-label="Download"
                :hidden-fields="$hiddenFields"
            />
        </div>

        <div class="border-t border-ui-border pt-4">
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-ui-fg-subtle">
                Auditor workload
            </p>
            <x-export.pdf-options
                variant="embedded"
                :heading="false"
                :action="route('admin.reports.auditor_activity_pdf')"
                submit-label="Download"
                :hidden-fields="$hiddenFields"
            />
        </div>
    </div>
</div>
