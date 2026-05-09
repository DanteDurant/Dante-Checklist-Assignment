<?php

namespace App\Application\Pdf;

use App\Enums\ChecklistInstanceStatus;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;

final class ComplianceSnapshotAssembler
{
    /**
     * @param  array{date_from?: string|null, date_to?: string|null}  $filters
     * @return array<string, mixed>
     */
    public function assemble(array $filters, string $detailLabel): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        $instanceQuery = ChecklistInstance::query();
        if ($from) {
            $instanceQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $instanceQuery->where('created_at', '<=', $to.' 23:59:59');
        }

        $byStatus = (clone $instanceQuery)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $completed = (clone $instanceQuery)
            ->whereIn('status', [ChecklistInstanceStatus::Submitted, ChecklistInstanceStatus::Approved])
            ->whereNotNull('submitted_at')
            ->count();

        $templateCount = ChecklistTemplate::query()->count();

        $auditorCount = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'auditor'))->count();

        $topTemplates = ChecklistTemplate::query()
            ->withCount(['instances as completed_count' => function ($q) use ($from, $to) {
                $q->whereIn('status', [ChecklistInstanceStatus::Submitted, ChecklistInstanceStatus::Approved])
                    ->whereNotNull('submitted_at');
                if ($from) {
                    $q->where('submitted_at', '>=', $from);
                }
                if ($to) {
                    $q->where('submitted_at', '<=', $to.' 23:59:59');
                }
            }])
            ->orderByDesc('completed_count')
            ->limit(8)
            ->get(['id', 'name', 'public_id', 'status']);

        $recentCompleted = ChecklistInstance::query()
            ->whereIn('status', [ChecklistInstanceStatus::Submitted, ChecklistInstanceStatus::Approved])
            ->whereNotNull('submitted_at')
            ->when($from, fn ($q) => $q->where('submitted_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('submitted_at', '<=', $to.' 23:59:59'))
            ->with(['template:id,name', 'auditor:id,name'])
            ->orderByDesc('submitted_at')
            ->limit(12)
            ->get();

        return [
            'detailLabel' => $detailLabel,
            'filters' => [
                'date_from' => $from,
                'date_to' => $to,
            ],
            'kpis' => [
                'templates' => $templateCount,
                'auditors' => $auditorCount,
                'instances_total' => (clone $instanceQuery)->count(),
                'completed_audits' => $completed,
            ],
            'byStatus' => $byStatus,
            'topTemplates' => $topTemplates,
            'recentCompleted' => $recentCompleted,
        ];
    }
}
