<x-layouts.auditor title="Auditor" heading="Dashboard">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Start a checklist" description="Choose a published template to begin.">
            <form method="GET" action="{{ route('auditor.dashboard') }}" class="space-y-4">
                <x-ui.field label="Filter templates" name="template_search">
                    <x-ui.input id="template_search" name="template_search" type="search" value="{{ old('template_search', $templateSearch ?? '') }}" autocomplete="off"
                                placeholder="Search by template title…" />
                </x-ui.field>

                <x-ui.field label="Search your instances" name="search">
                    <x-ui.input id="instance_search_field" name="search" type="search" value="{{ old('search', $search ?? '') }}" autocomplete="off"
                                placeholder="Template name or status…" />
                </x-ui.field>

                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="submit" data-loading-text="Applying…">Apply filters</x-ui.button>
                    @if(request()->filled('search') || request()->filled('template_search'))
                        <x-ui.button variant="secondary" :href="route('auditor.dashboard')">Clear all</x-ui.button>
                    @endif
                </div>
            </form>

            <div class="mt-6 border-t border-ui-border pt-6">
                <form method="GET" action="{{ route('auditor.start') }}" class="space-y-3">
                    <x-ui.field label="Template" name="template_id">
                        <x-ui.select id="template_id" name="template_id" required>
                            @forelse ($publishedTemplates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @empty
                                <option value="" disabled selected>No published templates match</option>
                            @endforelse
                        </x-ui.select>
                    </x-ui.field>

                    <div class="pt-1">
                        @if($publishedTemplates->isEmpty())
                            <x-ui.button type="button" class="w-full" variant="secondary" disabled>Start</x-ui.button>
                        @else
                            <x-ui.button type="submit" class="w-full">Start</x-ui.button>
                        @endif
                    </div>
                </form>
            </div>
        </x-ui.card>

        <div class="lg:col-span-2 space-y-6">
            <x-ui.disclosure title="Export my activity (PDF)">
                <x-export.pdf-options
                    variant="flush"
                    :heading="false"
                    :action="route('auditor.dashboard.export_pdf')"
                    submit-label="Download activity PDF"
                    :show-snapshot-dates="true"
                />
            </x-ui.disclosure>

            <x-ui.card title="Your checklist instances" description="Assigned to you. Filter using the card on the left.">
                <div class="space-y-3 sm:hidden">
                    @forelse ($instances as $i)
                        @php
                            $isEditable = in_array($i->status->value, ['draft', 'in_progress'], true);
                        @endphp
                        <x-ui.card>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-ui-fg">
                                        {{ $i->template?->name ?? ('Template #'.$i->checklist_template_id) }}
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <x-ui.status-badge :status="$i->status" />
                                        <span class="text-xs tabular-nums text-ui-fg-subtle">
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
                        <x-ui.empty-state
                            title="{{ request()->filled('search') ? 'No matching instances' : 'No instances' }}"
                            message="{{ request()->filled('search') ? 'Try another search or clear filters.' : 'You don’t have any checklist instances yet.' }}"
                        />
                    @endforelse
                </div>

                <div class="hidden sm:block">
                    <x-ui.table :headers="['Template', 'Status', 'Submitted', 'Action']">
                        @forelse ($instances as $i)
                            @php
                                $isEditable = in_array($i->status->value, ['draft', 'in_progress'], true);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-ui-fg">
                                    {{ $i->template?->name ?? ('Template #'.$i->checklist_template_id) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.status-badge :status="$i->status" />
                                </td>
                                <td class="px-4 py-3 text-sm tabular-nums text-ui-fg-muted">
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
                                <td colspan="4" class="px-4 py-6 text-sm text-ui-fg-muted">
                                    {{ request()->filled('search') ? 'No matching instances.' : 'No instances yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </div>

                <div class="mt-6 border-t border-ui-border pt-5">
                    {{ $instances->links() }}
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.auditor>
