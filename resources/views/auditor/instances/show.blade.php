<x-layouts.auditor :title="'Checklist: '.($instance->template?->name ?? $instance->id)" :heading="$instance->template?->name ?? 'Checklist Instance'">
    <x-ui.card title="Instance">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $instance->status->value }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $instance->submitted_at?->toDateTimeString() ?? '—' }}</dd>
            </div>
        </dl>

        <p class="mt-4 text-sm text-slate-600">
            This is a minimal placeholder page. The checklist answering UI is API-driven in this project.
        </p>

        <div class="mt-4 flex flex-wrap gap-2">
            <x-ui.button :href="route('auditor.dashboard')" variant="secondary">Back to dashboard</x-ui.button>
        </div>
    </x-ui.card>
</x-layouts.auditor>

