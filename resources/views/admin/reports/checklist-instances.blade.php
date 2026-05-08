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

                <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-2 pt-2">
                    <x-ui.button type="submit" data-loading-text="Applying...">Apply filters</x-ui.button>
                    <x-ui.button :href="route('admin.reports.checklist_instances')" variant="secondary">Reset</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="mt-6">
            <x-ui.card title="Results" description="Template, auditor, completion date, status.">
                <x-ui.table :headers="['Template', 'Auditor', 'Completion date', 'Status']">
                    @forelse ($results as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                {{ $row->template?->name ?? ('Template #'.$row->checklist_template_id) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                {{ $row->auditor?->name ?? ('User #'.$row->auditor_id) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                {{ $row->submitted_at?->toDateTimeString() ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                {{ $row->status->value }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6">
                                <x-ui.empty-state title="No results" message="Try adjusting your filters." />
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                <div class="mt-4">
                    {{ $results->links() }}
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>

