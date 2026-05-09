<?php

namespace App\Http\Requests\Api\Checklists;

use Illuminate\Foundation\Http\FormRequest;

class SaveDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\ChecklistInstance $checklist */
        $checklist = $this->route('checklist');

        return $this->user()?->can('update', $checklist) ?? false;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1', 'max:500'],
            'answers.*' => ['nullable'],
        ];
    }
}

