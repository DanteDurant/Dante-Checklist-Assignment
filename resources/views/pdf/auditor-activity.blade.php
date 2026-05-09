@extends('pdf.layout')

@section('pdf-body')
    @php /** @var \App\Enums\ExportDetailLevel $detailLevel */ @endphp

    <h2 class="section-title">Scope</h2>
    <dl class="meta-grid">
        <dt>Period</dt>
        <dd>
            {{ $filters['date_from'] ?? 'Any' }} — {{ $filters['date_to'] ?? 'Any' }}
        </dd>
        <dt>Audience</dt>
        <dd>
            @if (($scope ?? '') === 'single_auditor')
                Single auditor (your activity)
            @else
                All auditors (administrative digest)
            @endif
        </dd>
        @if (($filters['auditor_id'] ?? null) && ($scope ?? '') !== 'single_auditor')
            <dt>Filtered auditor ID</dt>
            <dd>{{ $filters['auditor_id'] }}</dd>
        @endif
    </dl>

    <h2 class="section-title">Auditor workload</h2>
    <table class="data-table">
        <thead>
        <tr>
            <th>Auditor</th>
            <th class="right">Instances</th>
            <th class="right">Completed</th>
            @if (($detailLevel ?? \App\Enums\ExportDetailLevel::Standard) === \App\Enums\ExportDetailLevel::Detailed)
                <th>Status mix</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($activity as $row)
            <tr>
                <td>
                    {{ $row['auditor']->name }}
                    <div class="muted small-print">{{ $row['auditor']->email }}</div>
                </td>
                <td class="right">{{ $row['total'] }}</td>
                <td class="right">{{ $row['completed'] }}</td>
                @if (($detailLevel ?? \App\Enums\ExportDetailLevel::Standard) === \App\Enums\ExportDetailLevel::Detailed)
                    <td class="small-print">
                        @foreach ($row['by_status'] as $st => $n)
                            {{ \App\Enums\ChecklistInstanceStatus::tryFrom((string) $st)?->label() ?? (string) $st }}: {{ $n }}@if(!$loop->last); @endif
                        @endforeach
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>

    @if (($detailLevel ?? \App\Enums\ExportDetailLevel::Standard) !== \App\Enums\ExportDetailLevel::Executive)
        <h2 class="section-title">Recent instance movement</h2>
        <p class="muted small-print">Latest updates within scope (max 40 rows).</p>
        <table class="data-table">
            <thead>
            <tr>
                <th>Instance</th>
                <th>Template</th>
                <th>Auditor</th>
                <th>Status</th>
                <th>Updated</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($recentInstances as $i)
                <tr>
                    <td class="mono muted">{{ $i->public_id }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($i->template?->name ?? '—', 56) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($i->auditor?->name ?? '—', 36) }}</td>
                    <td>{{ $i->status->label() }}</td>
                    <td>{{ optional($i->updated_at)->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="exec-narrative">
            Executive mode emphasizes workload distribution and completion throughput.
            Detailed movement tables are omitted; switch to Standard or Detailed export if required.
        </p>
    @endif
@endsection
