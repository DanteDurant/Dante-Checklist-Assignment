<x-layouts.admin title="Admin" heading="Dashboard">
    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Quick links">
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="url('/api/v1/admin/ping')" variant="secondary">Admin API ping</x-ui.button>
                <x-ui.button :href="url('/api/v1/checklist-templates')" variant="secondary">Templates API</x-ui.button>
                <x-ui.button :href="url('/api/v1/admin/reports/checklist-instances')" variant="secondary">Reports API</x-ui.button>
            </div>
            <p class="mt-3 text-sm text-slate-500">
                These links hit API routes; use Postman/curl with a token for JSON.
            </p>
        </x-ui.card>

        <x-ui.card title="Recent templates" description="Latest 10 templates (from DB).">
            <x-ui.table :headers="['Title', 'Status', 'Questions']">
                @forelse ($templates as $t)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $t->name }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $t->status->value }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $t->questions_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-sm text-slate-500">No templates yet.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.admin>

