<?php

namespace App\Http\Requests\Api\V1\ChecklistTemplates;

use App\Enums\ChecklistTemplateStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateChecklistTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\ChecklistTemplate $template */
        $template = $this->route('template');

        return $this->user()?->can('update', $template) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', new Enum(ChecklistTemplateStatus::class)],
        ];
    }
}

