<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sync vs queued thresholds
    |--------------------------------------------------------------------------
    |
    | Exports above these limits are queued so HTTP workers stay responsive.
    | Tune via environment variables in production.
    |
    */

    /** Completed checklist report: sync when matched rows are at or below this count */
    'sync_max_report_rows' => (int) env('PDF_EXPORT_SYNC_MAX_REPORT_ROWS', 75),

    /** Detailed reports queue sooner than standard */
    'detailed_report_row_floor' => (int) env('PDF_EXPORT_DETAILED_REPORT_ROW_FLOOR', 35),

    /** Max rows pulled into DomPDF for queued (background) report exports */
    'queued_report_row_cap' => (int) env('PDF_EXPORT_QUEUED_REPORT_ROW_CAP', 2000),

    /** Checklist instance PDF: sync when template question count is at or below this */
    'sync_max_checklist_questions' => (int) env('PDF_EXPORT_SYNC_MAX_CHECKLIST_QUESTIONS', 40),

    /** Instance exports with detailed audit level queue above this question count */
    'detailed_instance_question_floor' => (int) env('PDF_EXPORT_DETAILED_INSTANCE_QUESTION_FLOOR', 20),

    /** Template PDF: sync when question count is at or below this */
    'sync_max_template_questions' => (int) env('PDF_EXPORT_SYNC_MAX_TEMPLATE_QUESTIONS', 50),

    /** Compliance snapshot: queue when instances in optional date filter exceed this */
    'sync_max_snapshot_instances' => (int) env('PDF_EXPORT_SYNC_MAX_SNAPSHOT_INSTANCES', 800),

    /** Detailed snapshot queues above this instance count */
    'detailed_snapshot_instance_floor' => (int) env('PDF_EXPORT_DETAILED_SNAPSHOT_INSTANCE_FLOOR', 200),

    /** Admin auditor activity: sync when auditor rows at or below this */
    'sync_max_auditor_rows' => (int) env('PDF_EXPORT_SYNC_MAX_AUDITOR_ROWS', 15),

    /** Dedupe window: identical queued/processing exports within seconds reuse one job */
    'dedupe_ttl_seconds' => (int) env('PDF_EXPORT_DEDUPE_TTL_SECONDS', 600),

    /*
    |--------------------------------------------------------------------------
    | Compliance snapshot: avoid queue in local/dev
    |--------------------------------------------------------------------------
    |
    | When set to an integer, portfolio/compliance snapshot PDFs run synchronously
    | (no queue worker) as long as filtered instance count is at or below this cap.
    |
    | Default: 25_000 when APP_ENV=local so `php artisan queue:work` is not required for
    | dashboard exports. Set PDF_SNAPSHOT_FORCE_SYNC_MAX_INSTANCES=0 in .env to disable
    | (always use queue thresholds). In production, omit the env var (null) to use
    | sync_max_snapshot_instances / queue rules only.
    |
    */

    'compliance_snapshot_force_sync_max_instances' => env(
        'PDF_SNAPSHOT_FORCE_SYNC_MAX_INSTANCES',
        in_array(env('APP_ENV'), ['local'], true) ? 25000 : null,
    ),

    /** Log export lifecycle (enqueue, job start/complete) to default log channel */
    'log_lifecycle' => (bool) env('PDF_EXPORT_LOG_LIFECYCLE', true),

];
