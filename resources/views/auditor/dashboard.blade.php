<x-layouts.auditor title="Auditor" heading="Dashboard">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Start a checklist" description="Choose a published template to begin.">
            <form method="GET" action="{{ route('auditor.start') }}" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="template_id">Template</label>
                    <select id="template_id" name="template_id"
                            class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                        @foreach ($publishedTemplates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-1">
                    <x-ui.button type="submit" class="w-full">Start</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="lg:col-span-2">
            <x-ui.card title="Your checklist instances" description="Assigned to you.">
                <x-ui.table :headers="['Template', 'Status', 'Submitted', 'Action']">
                    @forelse ($instances as $i)
                        @php
                            $isEditable = in_array($i->status->value, ['draft', 'in_progress'], true);
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                {{ $i->template?->name ?? ('Template #'.$i->checklist_template_id) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($isEditable)
                                    <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">
                                        {{ $i->status->value === 'draft' ? 'Draft' : 'In progress' }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200">
                                        Completed
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $i->submitted_at?->toDateTimeString() ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($isEditable)
                                    <x-ui.button :href="route('auditor.instances.show', $i)" variant="secondary">Continue</x-ui.button>
                                @else
                                    <x-ui.button :href="route('auditor.instances.show', $i)" variant="secondary">View</x-ui.button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-sm text-slate-500">No instances yet.</td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </x-ui.card>
        </div>
    </div>
</x-layouts.auditor>

