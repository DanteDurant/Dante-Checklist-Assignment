<?php

namespace App\Http\Requests\Concerns;

trait TrimsQuestionText
{
    protected function prepareForValidation(): void
    {
        if ($this->has('question_text') && is_string($this->question_text)) {
            $this->merge(['question_text' => trim($this->question_text)]);
        }
    }
}
