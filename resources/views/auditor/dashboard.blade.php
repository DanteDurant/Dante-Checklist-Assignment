<x-layouts.auditor title="Auditor" heading="Dashboard">
    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Quick links">
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="url('/api/v1/auditor/ping')" variant="secondary">Auditor API ping</x-ui.button>
            </div>
            <p class="mt-3 text-sm text-slate-500">
                Checklist completion is API-driven; this UI shows your recent instances from the DB.
            </p>
        </x-ui.card>

        <x-ui.card title="Your recent checklist instances" description="Latest 10 instances (from DB).">
            <x-ui.table :headers="['Template', 'Status', 'Submitted']">
                @forelse ($instances as $i)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">
                            #{{ $i->checklist_template_id }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $i->status->value }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $i->submitted_at?->toDateTimeString() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-sm text-slate-500">No instances yet.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.auditor>

