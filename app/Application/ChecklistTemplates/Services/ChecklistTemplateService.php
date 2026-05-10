<?php

namespace App\Application\ChecklistTemplates\Services;

use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChecklistTemplateService
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return ChecklistTemplate::query()
            ->withCount('questions')
            ->when(trim((string) $search) !== '', fn (Builder $q) => $q->search($search))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{title:string, description?:string|null, status:string}  $data
     */
    public function create(array $data, User $actor): ChecklistTemplate
    {
        return DB::transaction(function () use ($data, $actor) {
            $template = new ChecklistTemplate;
            $template->public_id = (string) Str::ulid();
            $template->name = $data['title'];
            $template->description = $data['description'] ?? null;
            $template->status = ChecklistTemplateStatus::from($data['status']);
            $template->created_by = $actor->id;

            if ($template->status === ChecklistTemplateStatus::Published) {
                $template->published_at = now();
            }

            if ($template->status === ChecklistTemplateStatus::Archived) {
                $template->archived_at = now();
            }

            $template->save();

            return $template;
        });
    }

    /**
     * @param  array{title?:string, description?:string|null, status?:string}  $data
     */
    public function update(ChecklistTemplate $template, array $data): ChecklistTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            if (array_key_exists('title', $data)) {
                $template->name = $data['title'];
            }

            if (array_key_exists('description', $data)) {
                $template->description = $data['description'];
            }

            if (array_key_exists('status', $data)) {
                $next = ChecklistTemplateStatus::from($data['status']);
                $template->status = $next;

                if ($next === ChecklistTemplateStatus::Published && ! $template->published_at) {
                    $template->published_at = now();
                }

                if ($next === ChecklistTemplateStatus::Archived && ! $template->archived_at) {
                    $template->archived_at = now();
                }
            }

            $template->save();

            return $template->fresh();
        });
    }

    /**
     * Soft-delete the template: preserves checklist_instances (audit history) and FK integrity.
     * Related questions remain for referential consistency; the template is hidden from active UI/API lists.
     */
    public function delete(ChecklistTemplate $template): void
    {
        $template->delete();
    }
}
