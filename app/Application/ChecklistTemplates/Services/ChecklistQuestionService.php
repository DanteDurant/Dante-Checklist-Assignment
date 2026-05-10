<?php

namespace App\Application\ChecklistTemplates\Services;

use App\Enums\ChecklistQuestionType;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChecklistQuestionService
{
    public function paginate(ChecklistTemplate $template, int $perPage = 50, ?string $search = null): LengthAwarePaginator
    {
        $query = $template->questions()
            ->orderBy('sort_order')
            ->orderBy('id');

        if (trim((string) $search) !== '') {
            $query->search($search);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{question_text:string, answer_type:string, required?:bool, sort_order?:int, options?:array<int, array{value:string,label:string}>|null}  $data
     */
    public function create(ChecklistTemplate $template, array $data): ChecklistQuestion
    {
        try {
            return DB::transaction(function () use ($template, $data) {
                $question = new ChecklistQuestion;
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
        } catch (QueryException $e) {
            if ($this->isDuplicateDatabaseConstraint($e)) {
                throw ValidationException::withMessages([
                    'question_text' => ['That question already exists in this template.'],
                ]);
            }

            throw $e;
        }
    }

    /**
     * @param  array{question_text?:string, answer_type?:string, required?:bool, sort_order?:int, options?:array<int, array{value:string,label:string}>|null}  $data
     */
    public function update(ChecklistQuestion $question, array $data): ChecklistQuestion
    {
        try {
            return DB::transaction(function () use ($question, $data) {
                if (array_key_exists('question_text', $data)) {
                    $question->label = $data['question_text'];
                    $question->key = Str::snake(Str::limit($data['question_text'], 40, ''));
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
        } catch (QueryException $e) {
            if ($this->isDuplicateDatabaseConstraint($e)) {
                throw ValidationException::withMessages([
                    'question_text' => ['That question already exists in this template.'],
                ]);
            }

            throw $e;
        }
    }

    public function delete(ChecklistQuestion $question): void
    {
        $question->delete();
    }

    private function isDuplicateDatabaseConstraint(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? '';
        $driverCode = (int) ($e->errorInfo[1] ?? 0);

        return $sqlState === '23000'
            || $driverCode === 1062
            || str_contains(strtolower($e->getMessage()), 'duplicate');
    }
}
