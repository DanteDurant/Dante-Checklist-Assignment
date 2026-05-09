<?php

namespace App\Application\ChecklistTemplates\Services;

use App\Enums\ChecklistQuestionType;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChecklistQuestionService
{
    public function paginate(ChecklistTemplate $template, int $perPage = 50): LengthAwarePaginator
    {
        return $template->questions()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * @param array{question_text:string, answer_type:string, required?:bool, sort_order?:int, options?:array<int, array{value:string,label:string}>|null} $data
     */
    public function create(ChecklistTemplate $template, array $data): ChecklistQuestion
    {
        return DB::transaction(function () use ($template, $data) {
            $question = new ChecklistQuestion();
            $question->checklist_template_id = $template->id;
            $question->key = Str::snake(Str::limit($data['question_text'], 40, ''));
            $question->label = $data['question_text'];
            $question->type = ChecklistQuestionType::from($data['answer_type']);
            $question->is_required = (bool) ($data['required'] ?? false);
            $question->sort_order = (int) ($data['sort_order'] ?? 0);
            $question->options = $data['options'] ?? null;
            $question->is_active = true;
            $question->save();

            return $question;
        });
    }

    /**
     * @param array{question_text?:string, answer_type?:string, required?:bool, sort_order?:int, options?:array<int, array{value:string,label:string}>|null} $data
     */
    public function update(ChecklistQuestion $question, array $data): ChecklistQuestion
    {
        return DB::transaction(function () use ($question, $data) {
            if (array_key_exists('question_text', $data)) {
                $question->label = $data['question_text'];
            }

            if (array_key_exists('answer_type', $data)) {
                $question->type = ChecklistQuestionType::from($data['answer_type']);
            }

            if (array_key_exists('required', $data)) {
                $question->is_required = (bool) $data['required'];
            }

            if (array_key_exists('sort_order', $data)) {
                $question->sort_order = (int) $data['sort_order'];
            }

            if (array_key_exists('options', $data)) {
                $question->options = $data['options'];
            }

            $question->save();

            return $question->fresh();
        });
    }

    public function delete(ChecklistQuestion $question): void
    {
        $question->delete();
    }
}

