<x-layouts.auditor title="Auditor" heading="Dashboard">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Start a checklist" description="Choose a published template to begin.">
            <form method="GET" action="{{ route('auditor.start') }}" class="space-y-3">
                <x-ui.field label="Template" name="template_id">
                    <x-ui.select id="template_id" name="template_id">
                        @foreach ($publishedTemplates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <div class="pt-1">
                    <x-ui.button type="submit" class="w-full">Start</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="lg:col-span-2">
            <x-ui.card title="Your checklist instances" description="Assigned to you.">
                <div class="space-y-3 sm:hidden">
                    @forelse ($instances as $i)
                        @php
                            $isEditable = in_array($i->status->value, ['draft', 'in_progress'], true);
                        @endphp
                        <x-ui.card>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-900">
                                        {{ $i->template?->name ?? ('Template #'.$i->checklist_template_id) }}
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        @if ($isEditable)
                                            <x-ui.status-badge :status="$i->status" />
                                        @else
                                            <x-ui.status-badge :status="$i->status" />
                                        @endif
                                        <span class="text-xs text-slate-500">
                                            Submitted: {{ $i->submitted_at?->toDateTimeString() ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if ($isEditable)
                                        <x-ui.button :href="route('auditor.instances.show', $i)" variant="secondary">Continue</x-ui.button>
                                    @else
                                        <x-ui.button :href="route('auditor.instances.show', $i)" variant="secondary">View</x-ui.button>
                                    @endif
                                </div>
                            </div>
                        </x-ui.card>
                    @empty
                        <x-ui.empty-state title="No instances" message="You don’t have any checklist instances yet." />
                    @endforelse
                </div>

                <div class="hidden sm:block">
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
                                        <x-ui.status-badge :status="$i->status" />
                                    @else
                                        <x-ui.status-badge :status="$i->status" />
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
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.auditor>

