<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $documentTitle ?? 'Export' }}</title>
    @include('pdf.partials.styles')
</head>
<body>
<header class="pdf-header">
    <div class="pdf-header-left">
        <div class="pdf-app-name">{{ $appName }}</div>
        @isset($documentTitle)
            <div class="pdf-doc-title">{{ $documentTitle }}</div>
        @endisset
        @isset($documentSubtitle)
            <div class="pdf-doc-subtitle">{{ $documentSubtitle }}</div>
        @endisset
        @isset($detailLevel)
            <div class="pdf-doc-badge">Detail: {{ $detailLevel->label() }}</div>
        @endisset
    </div>
    <div class="pdf-header-right muted">
        <div><strong>Generated</strong></div>
        <div>{{ $generatedAt->format('Y-m-d H:i:s T') }}</div>
        @isset($confidential)
            <div class="pdf-confidential">{{ $confidential }}</div>
        @else
            <div class="pdf-confidential">Confidential</div>
        @endisset
    </div>
</header>

<main class="pdf-main">
    @yield('pdf-body')
</main>

<footer class="pdf-footer muted">
    <div>{{ $appName }} · Governance &amp; compliance record · Document ID {{ $generatedAt->format('Ymd-His') }}</div>
    <div class="pdf-page-hint">Multi-page exports: use your PDF viewer’s navigation for page numbers.</div>
</footer>
</body>
</html>
