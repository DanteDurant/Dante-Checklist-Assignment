<?php

namespace App\Application\Pdf;

use App\Enums\ChecklistInstanceStatus;
use App\Models\ChecklistInstance;
use App\Models\User;

final class AuditorActivityAssembler
{
    /**
     * @param  array{
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     auditor_id?: int|null
     * } $filters
     * @return array<string, mixed>
     */
    public function assemble(array $filters, string $detailLabel, ?int $scopeAuditorId = null): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;
        $auditorId = $scopeAuditorId ?? (isset($filters['auditor_id']) ? (int) $filters['auditor_id'] : null);

        $rows = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'auditor'))
            ->when($auditorId, fn ($q) => $q->whereKey($auditorId))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $activity = $rows->map(function (User $auditor) use ($from, $to) {
            $q = ChecklistInstance::query()->where('auditor_id', $auditor->id);
            if ($from) {
                $q->where('created_at', '>=', $from);
            }
            if ($to) {
                $q->where('created_at', '<=', $to.' 23:59:59');
            }

            $byStatus = (clone $q)
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status');

            $completed = (clone $q)
                ->whereIn('status', [ChecklistInstanceStatus::Submitted, ChecklistInstanceStatus::Approved])
                ->whereNotNull('submitted_at')
                ->count();

            return [
                'auditor' => $auditor,
                'total' => $q->count(),
                'completed' => $completed,
                'by_status' => $byStatus,
            ];
        });

        $recent = ChecklistInstance::query()
            ->when($auditorId, fn ($q) => $q->where('auditor_id', $auditorId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to.' 23:59:59'))
            ->with(['template:id,name', 'auditor:id,name,email'])
            ->orderByDesc('updated_at')
            ->limit(40)
            ->get();

        return [
            'detailLabel' => $detailLabel,
            'filters' => [
                'date_from' => $from,
                'date_to' => $to,
                'auditor_id' => $auditorId,
            ],
            'activity' => $activity,
            'recentInstances' => $recent,
            'scope' => $auditorId ? 'single_auditor' : 'all_auditors',
        ];
    }
}
