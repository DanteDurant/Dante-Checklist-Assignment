<x-layouts.admin title="Reports" heading="Completed Checklist Reports">
    <div class="max-w-5xl">
        <x-ui.card title="Filters" description="Only completed checklist instances are shown.">
            <form method="GET" action="{{ route('admin.reports.checklist_instances') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="date_from">Date from</label>
                    <input id="date_from" name="date_from" type="date" value="{{ old('date_from', $filters['date_from'] ?? '') }}"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900" />
                    @error('date_from')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="date_to">Date to</label>
                    <input id="date_to" name="date_to" type="date" value="{{ old('date_to', $filters['date_to'] ?? '') }}"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900" />
                    @error('date_to')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="template_id">Template</label>
                    <select id="template_id" name="template_id"
                            class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                        <option value="">All</option>
                        @foreach ($templates as $t)
                            <option value="{{ $t->id }}" @selected((string)($filters['template_id'] ?? '') === (string)$t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('template_id')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="auditor_id">Auditor</label>
                    <select id="auditor_id" name="auditor_id"
                            class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                        <option value="">All</option>
                        @foreach ($auditors as $a)
                            <option value="{{ $a->id }}" @selected((string)($filters['auditor_id'] ?? '') === (string)$a->id)>
                                {{ $a->name }} ({{ $a->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('auditor_id')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-2 pt-2">
                    <x-ui.button type="submit">Apply filters</x-ui.button>
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
                            <td colspan="4" class="px-4 py-6 text-sm text-slate-500">No results.</td>
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

