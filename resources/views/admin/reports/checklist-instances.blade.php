<x-layouts.admin title="Reports" heading="Completed Checklist Reports">
    <div class="max-w-5xl">
        <x-ui.card title="Filters" description="Only completed checklist instances are shown.">
            <form method="GET" action="{{ route('admin.reports.checklist_instances') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.field label="Date from" name="date_from">
                    <x-ui.input id="date_from" name="date_from" type="date" value="{{ old('date_from', $filters['date_from'] ?? '') }}" />
                </x-ui.field>

                <x-ui.field label="Date to" name="date_to">
                    <x-ui.input id="date_to" name="date_to" type="date" value="{{ old('date_to', $filters['date_to'] ?? '') }}" />
                </x-ui.field>

                <x-ui.field label="Template" name="template_id">
                    <x-ui.select id="template_id" name="template_id">
                        <option value="">All</option>
                        @foreach ($templates as $t)
                            <option value="{{ $t->id }}" @selected((string)($filters['template_id'] ?? '') === (string)$t->id)>{{ $t->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Auditor" name="auditor_id">
                    <x-ui.select id="auditor_id" name="auditor_id">
                        <option value="">All</option>
                        @foreach ($auditors as $a)
                            <option value="{{ $a->id }}" @selected((string)($filters['auditor_id'] ?? '') === (string)$a->id)>
                                {{ $a->name }} ({{ $a->email }})
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <div class="flex flex-wrap gap-2 pt-2 sm:col-span-2 lg:col-span-4">
                    <x-ui.button type="submit" data-loading-text="Applying...">Apply filters</x-ui.button>
                    <x-ui.button :href="route('admin.reports.checklist_instances')" variant="secondary">Reset</x-ui.button>
                </div>
            </form>

            <div class="mt-8 border-t border-ui-border pt-6">
                <x-export.reports-pdf-panel :hidden-fields="request()->except(['page'])" />
            </div>
        </x-ui.card>

        <div class="mt-6">
            <x-ui.card title="Results" description="Template, auditor, completion date, status.">
                <div class="space-y-3 sm:hidden">
                    @forelse ($results as $row)
                        <x-ui.card>
                            <div class="space-y-2">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Template</div>
                                    <div class="text-sm font-semibold text-ui-fg">
                                        {{ $row->template?->name ?? ('Template #'.$row->checklist_template_id) }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-2">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Auditor</div>
                                        <div class="text-sm text-ui-fg-muted">
                                            {{ $row->auditor?->name ?? ('User #'.$row->auditor_id) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Completion date</div>
                                        <div class="text-sm tabular-nums text-ui-fg-muted">{{ $row->submitted_at?->toDateTimeString() ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wider text-ui-fg-subtle">Status</div>
                                        <div class="text-sm"><x-ui.status-badge :status="$row->status" /></div>
                                    </div>
                                    @can('exportPdf', $row)
                                        <div class="pt-1">
                                            <x-ui.button
                                                class="w-full sm:w-auto"
                                                :href="route('admin.instances.export_pdf', $row)"
                                                variant="secondary"
                                                size="sm"
                                            >PDF</x-ui.button>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        </x-ui.card>
                    @empty
                        <x-ui.empty-state title="No results" message="Try adjusting your filters." />
                    @endforelse
                </div>

                <div class="hidden sm:block">
                    <x-ui.table :headers="['Template', 'Auditor', 'Completion date', 'Status', 'PDF']">
                        @forelse ($results as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-ui-fg">
                                    {{ $row->template?->name ?? ('Template #'.$row->checklist_template_id) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-ui-fg-muted">
                                    {{ $row->auditor?->name ?? ('User #'.$row->auditor_id) }}
                                </td>
                                <td class="px-4 py-3 text-sm tabular-nums text-ui-fg-muted">
                                    {{ $row->submitted_at?->toDateTimeString() ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.status-badge :status="$row->status" />
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @can('exportPdf', $row)
                                        <x-ui.button :href="route('admin.instances.export_pdf', $row)" variant="secondary" size="sm">PDF</x-ui.button>
                                    @else
                                        <span class="text-xs text-ui-fg-subtle">—</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6">
                                    <x-ui.empty-state title="No results" message="Try adjusting your filters." />
                                </td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </div>

                <div class="mt-6 border-t border-ui-border pt-5">
                    {{ $results->links() }}
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
