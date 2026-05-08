<?php

namespace App\Http\Resources\Api\V1\Auditor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ChecklistAnswer
 */
class ChecklistAnswerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->checklist_question_id,
            'version' => $this->version,
            'value' => $this->value,
            'is_not_applicable' => (bool) $this->is_not_applicable,
            'notes' => $this->notes,
            'answered_at' => $this->answered_at?->toISOString(),
        ];
    }
}

