<?php

namespace App\Http\Resources\Api\V1\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ChecklistInstance
 */
class ChecklistInstanceReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'status' => $this->status->value,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'template' => $this->whenLoaded('template', fn () => [
                'id' => $this->template->id,
                'public_id' => $this->template->public_id,
                'title' => $this->template->name,
            ]),
            'auditor' => $this->whenLoaded('auditor', fn () => [
                'id' => $this->auditor->id,
                'name' => $this->auditor->name,
                'email' => $this->auditor->email,
            ]),
            'current_version' => $this->current_version,
        ];
    }
}

