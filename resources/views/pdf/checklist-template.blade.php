@extends('pdf.layout')

@section('pdf-body')
    @php use Illuminate\Support\Str; @endphp
    @php
        $detailLevel = $detailLevel ?? \App\Enums\ExportDetailLevel::Standard;
    @endphp

    <h2 class="section-title">Template metadata</h2>
    <dl class="meta-grid">
        <dt>Name</dt>
        <dd>{{ $template->name }}</dd>
        <dt>Public ID</dt>
        <dd class="mono muted">{{ $template->public_id }}</dd>
        <dt>Status</dt>
        <dd class="meta-dd-chip"><table class="pdf-meta-chip pdf-meta-chip-muted" role="presentation"><tr><td>{{ $template->status->label() }}</td></tr></table></dd>
        @if ($template->description && $detailLevel !== \App\Enums\ExportDetailLevel::Summary)
            <dt>Description</dt>
            <dd>{{ Str::limit($template->description, $detailLevel === \App\Enums\ExportDetailLevel::Detailed ? 12000 : 4000) }}</dd>
        @endif
        @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
            <dt>Questions (active)</dt>
            <dd>{{ $questions->where('is_active', true)->count() }} / {{ $questions->count() }}</dd>
        @endif
    </dl>

    @if ($detailLevel === \App\Enums\ExportDetailLevel::Summary || $detailLevel === \App\Enums\ExportDetailLevel::Executive)
        <p class="exec-narrative">
            This {{ strtolower($detailLevel->label()) }} lists aggregate scope only.
            Use Standard or Detailed export for the printable questionnaire specification.
        </p>
        <table class="data-table compact">
            <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <tr><td>Total questions</td><td>{{ $questions->count() }}</td></tr>
            <tr><td>Required questions</td><td>{{ $questions->where('is_required', true)->count() }}</td></tr>
            <tr><td>Answer types in use</td><td>{{ $questions->pluck('type')->unique()->count() }}</td></tr>
            </tbody>
        </table>
    @else
        <h2 class="section-title">Question specification</h2>
        @if ($questions->isEmpty())
            <p class="muted">This template has no questions.</p>
        @else
            <table class="data-table">
                <thead>
                <tr>
                    <th style="width:6%">#</th>
                    @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                        <th style="width:14%">Key</th>
                    @endif
                    <th style="width:38%">Prompt</th>
                    <th style="width:12%">Type</th>
                    <th style="width:8%">Req</th>
                    <th style="width:8%">Active</th>
                    @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                        <th>Help</th>
                    @else
                        <th>Options preview</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach ($questions as $q)
                    @php
                        $optsPreview = '';
                        if (is_array($q->options) && count($q->options) > 0) {
                            $optsPreview = collect($q->options)->take(5)->map(function ($opt) {
                                if (is_array($opt)) {
                                    $l = $opt['label'] ?? $opt['value'] ?? '';

                                    return (string) $l;
                                }

                                return (string) $opt;
                            })->implode('; ');
                            $optsPreview = Str::limit($optsPreview, 180);
                            if (count($q->options) > 5) {
                                $optsPreview .= '…';
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $q->sort_order }}</td>
                        @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                            <td class="mono muted">{{ $q->key ?? '—' }}</td>
                        @endif
                        <td>{{ Str::limit($q->label, $detailLevel === \App\Enums\ExportDetailLevel::Detailed ? 900 : 500) }}</td>
                        <td>{{ str_replace('_', ' ', $q->type->value) }}</td>
                        <td>{{ $q->is_required ? 'Yes' : 'No' }}</td>
                        <td>{{ $q->is_active ? 'Yes' : 'No' }}</td>
                        @if ($detailLevel === \App\Enums\ExportDetailLevel::Detailed)
                            <td class="small-print">{{ $q->help_text ? Str::limit($q->help_text, 400) : '—' }}</td>
                        @else
                            <td>{{ $optsPreview !== '' ? $optsPreview : '—' }}</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @endif
@endsection
