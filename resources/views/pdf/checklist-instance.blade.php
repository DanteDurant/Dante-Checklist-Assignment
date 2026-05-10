@extends('pdf.layout')

@section('pdf-body')
    @php
        /** @var \App\Models\ChecklistInstance $instance */
        /** @var \App\Enums\ExportDetailLevel $detailLevel */
        $template = $instance->template;
        $completion = $meta['completion_date'] ?? null;
        $description = \Illuminate\Support\Str::limit((string) ($template?->description ?? ''), 4000);
        $sections = $sections ?? [];
    @endphp

    @if (!empty($sections['toc']))
        <h2 class="section-title">Contents</h2>
        <ol class="pdf-toc">
            @if (!empty($sections['metadata']))<li>Overview</li>@endif
            @if (!empty($sections['metrics']))<li>Coverage metrics</li>@endif
            @if (!empty($sections['timeline']))<li>Audit timeline</li>@endif
            @if (!empty($sections['findings']))<li>Findings &amp; indicators</li>@endif
            @if (!empty($sections['responses']))<li>Question responses</li>@endif
        </ol>
        <div class="page-break"></div>
    @endif

    @if (!empty($sections['metadata']))
        <h2 class="section-title" id="sec-overview">Checklist overview</h2>
        <dl class="meta-grid">
            <dt>Title</dt>
            <dd>{{ $template?->name ?? '—' }}</dd>
            @if ($description !== '')
                <dt>Description</dt>
                <dd>{{ $description }}</dd>
            @endif
            <dt>Template reference</dt>
            <dd><span class="muted">{{ $template?->public_id ?? '—' }}</span></dd>
            <dt>Instance ID</dt>
            <dd><span class="muted">{{ $instance->public_id }}</span></dd>
            <dt>Auditor</dt>
            <dd>
                {{ $instance->auditor?->name ?? '—' }}
                @if ($instance->auditor?->email)
                    <span class="muted">&lt;{{ $instance->auditor->email }}&gt;</span>
                @endif
            </dd>
            <dt>Completion date</dt>
            <dd>{{ $completion instanceof \Carbon\CarbonInterface ? $completion->format('Y-m-d H:i') : '—' }}</dd>
            <dt>Status</dt>
            {{-- Inline-table chip: DomPDF mis-sizes inline-block pills beside floated dt; td centers text reliably. --}}
            <dd class="meta-dd-chip"><table class="pdf-meta-chip" role="presentation"><tr><td>{{ $instance->status->label() }}</td></tr></table></dd>
            <dt>Record version</dt>
            <dd>{{ $instance->current_version }}</dd>
            @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                <dt>Template status</dt>
                <dd>{{ $template?->status->label() ?? '—' }}</dd>
            @endif
        </dl>
    @endif

    @if (!empty($sections['metrics']))
        <h2 class="section-title" id="sec-metrics">Response coverage</h2>
        <table class="data-table compact">
            <tbody>
            <tr><th style="width:40%">Active questions</th><td>{{ $metrics['questions_total'] ?? '—' }}</td></tr>
            <tr><th>Answered</th><td>{{ $metrics['questions_answered'] ?? '—' }}</td></tr>
            <tr><th>Required satisfied</th><td>{{ ($metrics['required_satisfied'] ?? 0) }} / {{ ($metrics['required_total'] ?? 0) }}</td></tr>
            <tr><th>Marked not applicable</th><td>{{ $metrics['not_applicable'] ?? '—' }}</td></tr>
            <tr><th>Coverage</th><td>{{ $metrics['coverage_pct'] ?? '—' }}%</td></tr>
            </tbody>
        </table>
    @endif

    @if (!empty($sections['timeline']))
        <h2 class="section-title" id="sec-timeline">Audit timeline</h2>
        @forelse ($timeline ?? [] as $ev)
            <div class="timeline-row"><strong>{{ $ev['label'] }}</strong> — {{ $ev['at'] }}</div>
        @empty
            <p class="muted">No timeline events recorded.</p>
        @endforelse
    @endif

    @if (!empty($sections['findings']))
        <h2 class="section-title" id="sec-findings">Findings &amp; risk indicators</h2>
        @php $findingsList = $findings ?? []; @endphp
        @forelse ($findingsList as $f)
            <div class="finding-row finding-{{ $f['severity'] }}">
                <strong>{{ $f['label'] }}</strong>
                <div class="muted">{{ $f['detail'] }}</div>
            </div>
        @empty
            <p class="muted">No automated findings flagged from responses (boolean “No” or gap-type disposition).</p>
        @endforelse

        @if ($detailLevel === \App\Enums\ExportDetailLevel::Executive)
            <p class="exec-narrative">
                This executive extract summarizes disposition signals auto-derived from structured responses.
                Refer to the detailed audit export for full evidentiary context.
            </p>
        @endif
    @endif

    @if (!empty($sections['responses']))
        <h2 class="section-title" id="sec-responses">Question responses</h2>
        @forelse ($rows as $row)
            @php
                /** @var \App\Models\ChecklistQuestion $q */
                $q = $row['question'];
                $ans = $row['answer'] ?? null;
            @endphp
            <div class="q-block" id="q-{{ $q->id }}">
                <div class="q-head">
                    @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                        <span class="muted mono">{{ $q->key ?? ('#'.$q->id) }}</span>
                    @endif
                    <span class="badge">{{ str_replace('_', ' ', $q->type->value) }}</span>
                    <span class="q-label">{{ $q->sort_order }}. {{ $q->label }}</span>
                    @if ($q->is_required)
                        <span class="required-mark" title="Required">*</span>
                    @endif
                    @if (!empty($row['riskFlag']))
                        <span class="risk-dot risk-{{ $row['riskFlag'] }}">●</span>
                    @endif
                </div>
                @if ($q->help_text && $detailLevel !== \App\Enums\ExportDetailLevel::Summary)
                    <div class="help">{{ $q->help_text }}</div>
                @endif
                @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed && $ans)
                    <dl class="mini-meta">
                        <dt>Answered</dt>
                        <dd>{{ optional($row['answeredAt'] ?? $ans->answered_at)->format('Y-m-d H:i:s') ?? '—' }}</dd>
                        <dt>Not applicable</dt>
                        <dd>{{ $ans->is_not_applicable ? 'Yes' : 'No' }}</dd>
                        @if ($ans->notes && trim((string)$ans->notes) !== '')
                            <dt>Auditor notes</dt>
                            <dd class="notes-block">{{ $ans->notes }}</dd>
                        @endif
                    </dl>
                    @if ($row['rawValueJson'] ?? null)
                        <div class="raw-value mono">{{ \Illuminate\Support\Str::limit($row['rawValueJson'], 2000) }}</div>
                    @endif
                @endif
                <div class="answer">{{ $row['answerText'] }}</div>
            </div>
        @empty
            <p class="muted">No active questions defined for this template.</p>
        @endforelse
    @elseif ($detailLevel === \App\Enums\ExportDetailLevel::Summary || $detailLevel === \App\Enums\ExportDetailLevel::Executive)
        <p class="muted small-print">
            Per-question responses are omitted in this {{ strtolower($detailLevel->label()) }} export.
            Run a Standard or Detailed export for full evidentiary responses.
        </p>
    @endif
@endsection
