<?php

namespace App\Http\Requests\Api\V1\Auditor;

use Illuminate\Foundation\Http\FormRequest;

class SaveChecklistProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\ChecklistInstance $instance */
        $instance = $this->route('instance');

        return $this->user()?->can('update', $instance) ?? false;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1', 'max:500'],
            'answers.*.question_id' => ['required', 'integer', 'distinct'],
            'answers.*.value' => ['present'],
            'answers.*.is_not_applicable' => ['sometimes', 'boolean'],
            'answers.*.notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}

