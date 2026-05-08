<?php

namespace App\Http\Resources\Api\V1\Auditor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ChecklistInstance
 */
class ChecklistInstanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'template_id' => $this->checklist_template_id,
            'auditor_id' => $this->auditor_id,
            'status' => $this->status->value,
            'current_version' => $this->current_version,
            'started_at' => $this->started_at?->toISOString(),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'finalized_at' => $this->finalized_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'answers' => ChecklistAnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}

