<style>
    /*
     | PDF print stylesheet — DomPDF-compatible (no flex/grid reliance).
     | Margins: use @page for consistent printable area; avoid double borders at block boundaries.
     */
    @page {
        /* Print margins — readable inset on all sides (compliance-style layout) */
        margin: 24mm 26mm 28mm 26mm;
    }

    * {
        box-sizing: border-box;
    }

    html {
        margin: 0;
        padding: 0;
        border: 0;
    }

    body {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.5;
        color: #1a1a1a;
        margin: 0;
        /* Bottom clearance for fixed footer */
        padding: 6px 0 52px 0;
        border: 0;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    /*
     | Header: do NOT use overflow:hidden — DomPDF clips the first line of floated/header text.
     | Extra padding-top safeguards ascenders on the first line of the document.
     */
    .pdf-header {
        width: 100%;
        margin: 0 0 22px 0;
        padding: 8px 0 18px 0;
        border: 0;
        border-top: none;
        border-bottom: none;
        overflow: visible;
    }

    .pdf-header::after {
        content: '';
        display: table;
        clear: both;
    }

    .pdf-header-left { float: left; max-width: 70%; }
    .pdf-header-right { float: right; max-width: 28%; text-align: right; }

    .pdf-app-name {
        font-size: 14px;
        line-height: 1.35;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-weight: 700;
        color: #334155;
        padding-top: 1px;
    }

    .pdf-doc-title {
        margin-top: 8px;
        font-size: 20px;
        line-height: 1.3;
        font-weight: 700;
        color: #0f172a;
    }

    .pdf-main {
        clear: both;
        padding-top: 4px;
        margin: 0;
        border: 0;
    }

    /* Footer: horizontal padding aligned with @page side margins */
    .pdf-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 10px 26mm 6px 26mm;
        margin: 0;
        font-size: 9px;
        line-height: 1.4;
        border: 0;
        border-top: none;
    }

    .pdf-footer div { margin: 0 0 2px 0; }

    h2.section-title {
        font-size: 14px;
        line-height: 1.35;
        font-weight: 700;
        color: #0f172a;
        margin: 20px 0 10px 0;
        padding: 2px 0 6px 0;
        border-bottom: 1px solid #e2e8f0;
        page-break-after: avoid;
    }

    /* First section after header: keep breathing room so DomPDF doesn’t clip the heading */
    h2.section-title:first-child {
        margin-top: 2px;
        padding-top: 4px;
    }

    dl.meta-grid {
        display: block;
        margin: 0 0 12px 0;
    }

    dl.meta-grid dt {
        float: left;
        clear: left;
        width: 140px;
        font-weight: 700;
        color: #475569;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding-top: 6px;
        padding-bottom: 6px;
        line-height: 1.35;
    }

    dl.meta-grid dd {
        margin-left: 150px;
        margin-bottom: 10px;
        padding-top: 5px;
        padding-bottom: 5px;
        line-height: 1.35;
        color: #0f172a;
    }

    .muted { color: #64748b; }

    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 10px;
    }

    table.data-table th {
        text-align: left;
        padding: 8px;
        border: 1px solid #cbd5e1;
        background: #f1f5f9;
        color: #334155;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    table.data-table td {
        padding: 9px 10px;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }

    /* Status column + pills: center in row (DomPDF-safe; avoid flex) */
    table.data-table td.cell-status {
        vertical-align: middle;
    }

    tr { page-break-inside: avoid; }

    .q-block {
        margin-bottom: 14px;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 2px;
        page-break-inside: avoid;
    }

    .q-label { font-weight: 700; color: #0f172a; font-size: 11px; }

    .badge {
        font-size: 9px;
        padding: 2px 6px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        color: #334155;
    }

    .required-mark { color: #b45309; font-weight: 700; }
    .help { font-size: 10px; color: #64748b; margin-top: 2px; }

    .answer {
        margin-top: 8px;
        padding: 8px;
        background: #f8fafc;
        border-left: 3px solid #0f172a;
        white-space: pre-wrap;
    }

    .pdf-doc-subtitle {
        margin-top: 8px;
        font-size: 11px;
        line-height: 1.45;
        color: #475569;
        font-weight: 600;
    }

    .pdf-doc-badge {
        margin-top: 8px;
        display: inline-block;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .pdf-confidential {
        margin-top: 6px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #b45309;
    }

    .pdf-page-hint { font-size: 8px; margin-top: 4px; color: #94a3b8; }

    table.data-table.compact td, table.data-table.compact th { padding: 6px; font-size: 10px; }

    td.right, th.right { text-align: right; }

    .mono { font-family: DejaVu Sans Mono, monospace; font-size: 9px; }

    .small-print { font-size: 9px; line-height: 1.35; }

    .page-break { page-break-before: always; }

    .timeline-row { margin-bottom: 6px; padding-left: 8px; border-left: 2px solid #e2e8f0; }

    .finding-row { margin-bottom: 8px; padding: 8px; border-radius: 2px; border: 1px solid #e2e8f0; }

    .finding-attention { border-left: 4px solid #f59e0b; background: #fffbeb; }

    .finding-risk { border-left: 4px solid #dc2626; background: #fef2f2; }

    .exec-narrative {
        margin: 12px 0;
        padding: 10px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        font-size: 10px;
        color: #334155;
    }

    .status-pill {
        display: inline-block;
        vertical-align: middle;
        padding: 5px 12px;
        line-height: 1.35;
        border-radius: 999px;
        background: #e0f2fe;
        border: 1px solid #7dd3fc;
        font-weight: 700;
        font-size: 10px;
        text-align: center;
    }

    /* Template lifecycle / neutral emphasis (published, draft, …) */
    .status-pill-muted {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #334155;
    }

    .q-head { margin-bottom: 4px; }

    .risk-dot { font-size: 12px; margin-left: 4px; }

    .risk-attention { color: #d97706; }

    .risk-risk { color: #dc2626; }

    dl.mini-meta { margin: 8px 0 0 0; font-size: 9px; }

    dl.mini-meta dt {
        float: left;
        clear: left;
        width: 110px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
    }

    dl.mini-meta dd { margin-left: 115px; margin-bottom: 4px; }

    .notes-block { white-space: pre-wrap; }

    .raw-value {
        margin-top: 6px;
        padding: 6px;
        background: #f1f5f9;
        font-size: 8px;
        word-break: break-all;
    }

    ol.pdf-toc { margin: 0 0 16px 18px; padding: 0; }

    ol.pdf-toc li { margin-bottom: 4px; }
</style>
