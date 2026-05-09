<?php

namespace App\Http\Requests\Api\V1\ChecklistTemplates;

use App\Enums\ChecklistQuestionType;
use App\Models\ChecklistQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateChecklistQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\ChecklistQuestion $question */
        $question = $this->route('question');

        return $this->user()?->can('update', $question->template) ?? false;
    }

    public function rules(): array
    {
        return [
            'question_text' => ['sometimes', 'required', 'string', 'max:5000'],
            'answer_type' => ['sometimes', 'required', new Enum(ChecklistQuestionType::class)],
            'required' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'options' => ['sometimes', 'nullable', 'array', 'max:200'],
            'options.*.value' => ['required_with:options', 'string', 'max:255'],
            'options.*.label' => ['required_with:options', 'string', 'max:255'],
        ];
    }
}

