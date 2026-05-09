<?php

namespace Tests\Unit;

use App\Enums\ExportDetailLevel;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Services\Exports\ExportQueueDecision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pure threshold logic for sync vs queued exports (no HTTP).
 */
final class ExportQueueDecisionTest extends TestCase
{
    use RefreshDatabase;

    private ExportQueueDecision $decision;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decision = new ExportQueueDecision;
    }

    public function test_report_queues_when_matched_rows_exceed_sync_max(): void
    {
        config(['pdf_exports.sync_max_report_rows' => 10]);

        $this->assertTrue($this->decision->shouldQueueChecklistReport(11, ExportDetailLevel::Standard));
        $this->assertFalse($this->decision->shouldQueueChecklistReport(10, ExportDetailLevel::Standard));
    }

    public function test_instance_queues_when_question_count_exceeds_threshold(): void
    {
        config(['pdf_exports.sync_max_checklist_questions' => 5]);

        $template = ChecklistTemplate::factory()->published()->create();
        ChecklistQuestion::factory()->count(6)->create([
            'checklist_template_id' => $template->id,
            'is_active' => true,
        ]);

        $instance = ChecklistInstance::factory()->create([
            'checklist_template_id' => $template->id,
        ]);

        $this->assertTrue($this->decision->shouldQueueChecklistInstance($instance, ExportDetailLevel::Standard));
    }
}
