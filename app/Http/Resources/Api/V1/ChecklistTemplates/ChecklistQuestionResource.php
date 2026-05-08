<?php

namespace App\Http\Resources\Api\V1\ChecklistTemplates;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ChecklistQuestion
 */
class ChecklistQuestionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'checklist_template_id' => $this->checklist_template_id,
            'question_text' => $this->label,
            'answer_type' => $this->type->value,
            'required' => (bool) $this->is_required,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

