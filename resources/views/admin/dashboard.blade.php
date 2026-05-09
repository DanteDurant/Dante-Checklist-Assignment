<x-layouts.admin title="Admin" heading="Dashboard">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Total templates">
            <div class="text-3xl font-semibold tabular-nums text-ui-fg">{{ number_format($totalTemplates) }}</div>
            <p class="mt-1 text-sm leading-relaxed text-ui-fg-muted">Checklist templates in the system.</p>
        </x-ui.card>

        <x-ui.card title="Total audits completed">
            <div class="text-3xl font-semibold tabular-nums text-ui-fg">{{ number_format($totalAuditsCompleted) }}</div>
            <p class="mt-1 text-sm leading-relaxed text-ui-fg-muted">Submitted/approved checklist instances.</p>
        </x-ui.card>

        <x-ui.card title="Total auditors">
            <div class="text-3xl font-semibold tabular-nums text-ui-fg">{{ number_format($totalAuditors) }}</div>
            <p class="mt-1 text-sm leading-relaxed text-ui-fg-muted">Users with the auditor role.</p>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Navigation" description="Jump to key admin modules.">
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('admin.templates.index')" variant="secondary">Templates</x-ui.button>
                <x-ui.button :href="route('admin.reports.checklist_instances')" variant="secondary">Reports</x-ui.button>
            </div>
        </x-ui.card>

        <div class="lg:col-span-2 space-y-6">
            <x-ui.disclosure title="Portfolio PDF snapshot">
                <x-export.pdf-options
                    variant="flush"
                    :heading="false"
                    :action="route('admin.dashboard.export_pdf')"
                    submit-label="Download snapshot"
                    :show-snapshot-dates="true"
                />
            </x-ui.disclosure>

            <x-ui.card title="Recent templates" description="Latest 10 templates (from DB).">
                <div class="space-y-3 sm:hidden">
                    @forelse ($templates as $t)
                        <x-ui.card>
                            <div class="text-sm font-semibold text-ui-fg">{{ $t->name }}</div>
                            <div class="mt-1 text-sm text-ui-fg-muted">
                                <x-ui.status-badge :status="$t->status" /> · Questions: {{ $t->questions_count }}
                            </div>
                        </x-ui.card>
                    @empty
                        <x-ui.empty-state title="No templates yet" message="Create your first checklist template to get started." />
                    @endforelse
                </div>

                <div class="hidden sm:block">
                    <x-ui.table :headers="['Title', 'Status', 'Questions']">
                        @forelse ($templates as $t)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-ui-fg">{{ $t->name }}</td>
                                <td class="px-4 py-3 text-sm text-ui-fg-muted"><x-ui.status-badge :status="$t->status" /></td>
                                <td class="px-4 py-3 text-sm tabular-nums text-ui-fg-muted">{{ $t->questions_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-sm text-ui-fg-muted">No templates yet.</td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
