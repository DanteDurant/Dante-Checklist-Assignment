<?php

namespace App\Http\Requests\Web\Admin;

use App\Enums\ChecklistQuestionType;
use App\Http\Requests\Concerns\TrimsQuestionText;
use App\Models\ChecklistTemplate;
use App\Rules\UniqueQuestionLabelInTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreChecklistQuestionRequest extends FormRequest
{
    use TrimsQuestionText;

    public function authorize(): bool
    {
        /** @var ChecklistTemplate $template */
        $template = $this->route('template');

        return $this->user()?->can('update', $template) ?? false;
    }

    public function rules(): array
    {
        /** @var ChecklistTemplate $template */
        $template = $this->route('template');

        return [
            'question_text' => [
                'required',
                'string',
                'max:5000',
                new UniqueQuestionLabelInTemplate($template),
            ],
            'answer_type' => ['required', new Enum(ChecklistQuestionType::class)],
            'required' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'options_text' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'question_text' => 'question text',
            'answer_type' => 'answer type',
            'sort_order' => 'sort order',
            'options_text' => 'options',
        ];
    }
}
