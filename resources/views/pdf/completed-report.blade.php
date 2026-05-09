@extends('pdf.layout')

@section('pdf-body')
    @php use Illuminate\Support\Str; @endphp
    @php
        /** @var \App\Enums\ExportDetailLevel $detailLevel */
        $detailLevel = $detailLevel ?? \App\Enums\ExportDetailLevel::Standard;
        $rows = $reportRows ?? $results;
    @endphp

    <h2 class="section-title">Applied filters</h2>
    <dl class="meta-grid">
        <dt>Date from</dt>
        <dd>{{ $summaries['date_from'] ?? 'Any' }}</dd>
        <dt>Date to</dt>
        <dd>{{ $summaries['date_to'] ?? 'Any' }}</dd>
        <dt>Template</dt>
        <dd>{{ $summaries['template'] ?? 'Any' }}</dd>
        <dt>Auditor</dt>
        <dd>{{ $summaries['auditor'] ?? 'Any' }}</dd>
        <dt>Matches (dataset)</dt>
        <dd>
            {{ $totalMatched ?? $results->count() }} total
            @if ($truncated ?? false)
                <span class="muted"> — exported batch capped at {{ $limit }} rows</span>
            @endif
        </dd>
        @isset($summaries['q'])
            @if ($summaries['q'] !== null && trim((string) $summaries['q']) !== '')
                <dt>Search</dt>
                <dd>{{ Str::limit((string) $summaries['q'], 200) }}</dd>
            @endif
        @endisset
    </dl>

    @if ($detailLevel === \App\Enums\ExportDetailLevel::Executive || $detailLevel === \App\Enums\ExportDetailLevel::Summary)
        <h2 class="section-title">Portfolio indicators</h2>
        <table class="data-table compact">
            <thead>
            <tr>
                <th>Status</th>
                <th class="right">Count (in export batch)</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($statusCounts ?? [] as $st => $c)
                <tr>
                    <td>{{ \App\Enums\ChecklistInstanceStatus::tryFrom((string) $st)?->label() ?? (string) $st }}</td>
                    <td class="right">{{ $c }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($detailLevel === \App\Enums\ExportDetailLevel::Executive)
            <p class="exec-narrative">
                This executive export emphasizes aggregate posture across the filtered completed audits.
                Individual instance identifiers below may be truncated for readability.
            </p>
        @endif
    @endif

    <h2 class="section-title">
        @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
            Detailed register
        @elseif ($detailLevel === \App\Enums\ExportDetailLevel::Executive)
            Representative completions (preview)
        @else
            Completed checklist summaries
        @endif
    </h2>

    @if ($rows->isEmpty())
        <p>No checklist instances matched the applied filters.</p>
    @else
        <table class="data-table">
            <thead>
            <tr>
                <th style="width:26%">Template</th>
                <th style="width:20%">Auditor</th>
                @if ($detailLevel !== \App\Enums\ExportDetailLevel::Summary)
                    <th style="width:14%">Instance</th>
                @endif
                <th style="width:18%">Completion</th>
                <th style="width:12%">Status</th>
                @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                    <th>Version</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ Str::limit($row->template?->name ?? ('Template #'.$row->checklist_template_id), 120) }}</td>
                    <td>{{ Str::limit($row->auditor?->name ?? ('#'.$row->auditor_id), 80) }}</td>
                    @if ($detailLevel !== \App\Enums\ExportDetailLevel::Summary)
                        <td class="mono muted">{{ $row->public_id }}</td>
                    @endif
                    <td>{{ optional($row->submitted_at)->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $row->status->label() }}</td>
                    @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                        <td>{{ $row->current_version }}</td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>

        @if ($detailLevel === \App\Enums\ExportDetailLevel::Executive && ($totalMatched ?? 0) > ($executivePreviewCap ?? 35))
            <p class="muted small-print">
                Showing first {{ $rows->count() }} of {{ $totalMatched }} matching records.
                Use Standard or Detailed export for the full register (subject to the {{ $limit }} row safety cap).
            </p>
        @endif
    @endif
@endsection
