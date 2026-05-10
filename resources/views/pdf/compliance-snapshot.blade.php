@extends('pdf.layout')

@section('pdf-body')
    @php /** @var \App\Enums\ExportDetailLevel $detailLevel */ @endphp

    <h2 class="section-title">Scope</h2>
    <dl class="meta-grid">
        <dt>Reporting window</dt>
        <dd>
            @if ($filters['date_from'] ?? null)
                {{ $filters['date_from'] }}
            @else
                Any
            @endif
            —
            @if ($filters['date_to'] ?? null)
                {{ $filters['date_to'] }}
            @else
                Any
            @endif
        </dd>
        <dt>Snapshot depth</dt>
        <dd>{{ $detailLabel }}</dd>
    </dl>

    <h2 class="section-title">Key performance indicators</h2>
    <table class="data-table compact">
        <tbody>
        <tr><th style="width:45%">Published templates (catalog)</th><td>{{ $kpis['templates'] }}</td></tr>
        <tr><th>Registered auditors</th><td>{{ $kpis['auditors'] }}</td></tr>
        <tr><th>Checklist instances (scoped)</th><td>{{ $kpis['instances_total'] }}</td></tr>
        <tr><th>Completed audits (submitted/approved)</th><td>{{ $kpis['completed_audits'] }}</td></tr>
        </tbody>
    </table>

    <h2 class="section-title">Instances by lifecycle status</h2>
    <table class="data-table">
        <thead>
        <tr><th>Status</th><th class="right">Count</th></tr>
        </thead>
        <tbody>
        @forelse ($byStatus ?? [] as $st => $c)
            <tr>
                <td class="cell-status"><span class="status-pill">{{ \App\Enums\ChecklistInstanceStatus::tryFrom((string) $st)?->label() ?? (string) $st }}</span></td>
                <td class="right">{{ $c }}</td>
            </tr>
        @empty
            <tr><td colspan="2" class="muted">No instance data.</td></tr>
        @endforelse
        </tbody>
    </table>

    @if (($detailLevel ?? \App\Enums\ExportDetailLevel::Standard) !== \App\Enums\ExportDetailLevel::Summary)
        <h2 class="section-title">Templates by completion volume</h2>
        <table class="data-table">
            <thead>
            <tr>
                <th>Template</th>
                <th class="right">Completed count</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($topTemplates as $t)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($t->name, 90) }}</td>
                    <td class="right">{{ $t->completed_count }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if (($detailLevel ?? \App\Enums\ExportDetailLevel::Standard) !== \App\Enums\ExportDetailLevel::Summary)
        <h2 class="section-title">Recent completions</h2>
        <table class="data-table">
            <thead>
            <tr>
                <th>Template</th>
                <th>Auditor</th>
                <th>Completed</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($recentCompleted as $row)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($row->template?->name ?? '—', 70) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($row->auditor?->name ?? '—', 40) }}</td>
                    <td>{{ optional($row->submitted_at)->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="cell-status"><span class="status-pill">{{ $row->status->label() }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
