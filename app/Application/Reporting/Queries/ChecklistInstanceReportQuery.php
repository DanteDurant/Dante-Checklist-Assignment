<?php

namespace App\Application\Reporting\Queries;

use App\Enums\ChecklistInstanceStatus;
use App\Models\ChecklistInstance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ChecklistInstanceReportQuery
{
    /**
     * Scalable reporting query:
     * - Whitelisted filters only
     * - Avoids N+1 via eager loading
     * - Uses submitted_at + status indexes
     *
     * @param array{
     *   date_from?: string|null,
     *   date_to?: string|null,
     *   template_id?: int|null,
     *   auditor_id?: int|null,
     *   q?: string|null,
     *   per_page?: int|null
     * } $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        return $this->baseQuery($filters)
            ->with([
                'template:id,name,public_id',
                'auditor:id,name,email',
            ])
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Non-paginated rows for PDF export (capped to keep DomPDF predictable on large archives).
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, ChecklistInstance>
     */
    public function limitedCollection(array $filters, int $limit = 500): Collection
    {
        $limit = max(1, min($limit, 500));

        return $this->baseQuery($filters)
            ->with([
                'template:id,name,public_id',
                'auditor:id,name,email',
            ])
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function matchedCount(array $filters): int
    {
        return (int) $this->baseQuery($filters)->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        $query = ChecklistInstance::query()
            ->select([
                'id',
                'public_id',
                'checklist_template_id',
                'auditor_id',
                'status',
                'current_version',
                'started_at',
                'submitted_at',
                'finalized_at',
                'created_at',
                'updated_at',
            ])
            // Only "completed" instances.
            ->whereIn('status', [ChecklistInstanceStatus::Submitted, ChecklistInstanceStatus::Approved])
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if (! empty($filters['template_id'])) {
            $query->where('checklist_template_id', (int) $filters['template_id']);
        }

        if (! empty($filters['auditor_id'])) {
            $query->where('auditor_id', (int) $filters['auditor_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('submitted_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('submitted_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['q'])) {
            $needle = trim((string) $filters['q']);

            if ($needle !== '') {
                // Search strategy: match auditor name/email or template name.
                $query->where(function (Builder $q) use ($needle) {
                    $q->whereHas('auditor', function (Builder $auditor) use ($needle) {
                        $auditor
                            ->where('name', 'like', "%{$needle}%")
                            ->orWhere('email', 'like', "%{$needle}%");
                    })->orWhereHas('template', function (Builder $template) use ($needle) {
                        $template->where('name', 'like', "%{$needle}%");
                    });
                });
            }
        }

        return $query;
    }
}
