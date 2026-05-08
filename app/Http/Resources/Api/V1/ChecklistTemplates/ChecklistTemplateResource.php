<?php

namespace App\Http\Resources\Api\V1\ChecklistTemplates;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ChecklistTemplate
 */
class ChecklistTemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'title' => $this->name,
            'description' => $this->description,
            'status' => $this->status->value,
            'questions_count' => $this->when(isset($this->questions_count), $this->questions_count),
            'published_at' => $this->published_at?->toISOString(),
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'questions' => ChecklistQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}

