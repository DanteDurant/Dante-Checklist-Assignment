<?php

namespace App\Http\Requests\Api\V1\Auditor;

use Illuminate\Foundation\Http\FormRequest;

class StartChecklistInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('auditor') ?? false;
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'integer', 'exists:checklist_templates,id'],
        ];
    }
}

