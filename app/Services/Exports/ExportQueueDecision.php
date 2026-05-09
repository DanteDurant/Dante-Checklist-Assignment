<?php

namespace App\Services\Exports;

use App\Enums\ExportDetailLevel;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;

final class ExportQueueDecision
{
    public function shouldQueueChecklistInstance(ChecklistInstance $instance, ExportDetailLevel $detail): bool
    {
        $n = $this->activeQuestionCountForInstance($instance);

        $syncMax = (int) config('pdf_exports.sync_max_checklist_questions', 40);
        $detailedFloor = (int) config('pdf_exports.detailed_instance_question_floor', 20);

        if ($detail === ExportDetailLevel::Detailed && $n > $detailedFloor) {
            return true;
        }

        return $n > $syncMax;
    }

    public function shouldQueueChecklistReport(int $matchedCount, ExportDetailLevel $detail): bool
    {
        $syncMax = (int) config('pdf_exports.sync_max_report_rows', 75);
        $detailedFloor = (int) config('pdf_exports.detailed_report_row_floor', 35);

        if ($matchedCount > $syncMax) {
            return true;
        }

        return $detail === ExportDetailLevel::Detailed && $matchedCount > $detailedFloor;
    }

    public function shouldQueueChecklistTemplate(ChecklistTemplate $template, ExportDetailLevel $detail): bool
    {
        $template->loadCount(['questions']);

        $n = (int) $template->questions_count;

        $syncMax = (int) config('pdf_exports.sync_max_template_questions', 50);

        if ($detail === ExportDetailLevel::Detailed && $n > max(10, (int) floor($syncMax / 2))) {
            return true;
        }

        return $n > $syncMax;
    }

    public function shouldQueueComplianceSnapshot(int $instanceCount, ExportDetailLevel $detail): bool
    {
        $syncMax = (int) config('pdf_exports.sync_max_snapshot_instances', 800);
        $detailFloor = (int) config('pdf_exports.detailed_snapshot_instance_floor', 200);

        if ($instanceCount > $syncMax) {
            return true;
        }

        return $detail === ExportDetailLevel::Detailed && $instanceCount > $detailFloor;
    }

    /**
     * @param  'admin'|'self'  $scope
     */
    public function shouldQueueAuditorActivity(int $auditorRowCount, ExportDetailLevel $detail, string $scope): bool
    {
        if ($scope === 'self') {
            return $detail === ExportDetailLevel::Detailed;
        }

        $syncMax = (int) config('pdf_exports.sync_max_auditor_rows', 15);

        if ($auditorRowCount > $syncMax) {
            return true;
        }

        return $detail === ExportDetailLevel::Detailed && $auditorRowCount > max(4, (int) floor($syncMax / 3));
    }

    public function snapshotFilteredInstanceCount(?string $dateFrom, ?string $dateTo): int
    {
        $q = ChecklistInstance::query();
        if ($dateFrom) {
            $q->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $q->where('created_at', '<=', $dateTo.' 23:59:59');
        }

        return (int) $q->count();
    }

    public function adminAuditorRowCount(?int $auditorId): int
    {
        return (int) User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'auditor'))
            ->when($auditorId, fn ($q) => $q->whereKey($auditorId))
            ->count();
    }

    private function activeQuestionCountForInstance(ChecklistInstance $instance): int
    {
        return (int) ChecklistQuestion::query()
            ->where('checklist_template_id', $instance->checklist_template_id)
            ->where('is_active', true)
            ->count();
    }
}
