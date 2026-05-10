<?php

namespace App\Rules;

use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Support\QuestionTextNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueQuestionLabelInTemplate implements ValidationRule
{
    public function __construct(
        private readonly ChecklistTemplate $template,
        private readonly ?int $exceptQuestionId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $normalized = QuestionTextNormalizer::normalize($value);

        if ($normalized === '') {
            return;
        }

        $query = ChecklistQuestion::query()
            ->where('checklist_template_id', $this->template->id);

        if ($this->exceptQuestionId !== null) {
            $query->where('id', '!=', $this->exceptQuestionId);
        }

        foreach ($query->pluck('label') as $existingLabel) {
            if (QuestionTextNormalizer::normalize((string) $existingLabel) === $normalized) {
                $fail('That question already exists in this template.');

                return;
            }
        }
    }
}
