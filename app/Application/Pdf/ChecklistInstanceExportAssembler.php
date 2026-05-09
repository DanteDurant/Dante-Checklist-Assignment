<?php

namespace App\Application\Pdf;

use App\Enums\ChecklistQuestionType;
use App\Enums\ExportDetailLevel;
use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use Illuminate\Support\Collection;

final class ChecklistInstanceExportAssembler
{
    public function __construct(
        private readonly AnswerTextFormatter $formatter,
    ) {}

    /**
     * @param  array<string, bool>|null  $sections  When null, derived from detail level.
     * @return array<string, mixed>
     */
    public function assemble(
        ChecklistInstance $instance,
        ExportDetailLevel $detail = ExportDetailLevel::Standard,
        ?array $sections = null,
    ): array {
        $instance->loadMissing([
            'template:id,name,description,public_id,status,created_by',
            'auditor:id,name,email',
        ]);

        $sections = $sections ?? $this->defaultSections($detail);

        $questions = ChecklistQuestion::query()
            ->where('checklist_template_id', $instance->checklist_template_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /** @var Collection<int, ChecklistAnswer> $answers */
        $answers = ChecklistAnswer::query()
            ->where('checklist_instance_id', $instance->id)
            ->where('version', $instance->current_version)
            ->get()
            ->keyBy('checklist_question_id');

        $rows = $questions->map(function (ChecklistQuestion $question) use ($answers, $detail) {
            $answer = $answers->get($question->id);
            $appendNotes = $detail !== ExportDetailLevel::Detailed;

            return [
                'question' => $question,
                'answer' => $answer,
                'answerText' => $this->formatter->format($question, $answer, $appendNotes),
                'answeredAt' => $answer?->answered_at,
                'validationOk' => $this->validationOk($question, $answer),
                'rawValueJson' => $answer !== null && is_array($answer->value)
                    ? json_encode($answer->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : null,
                'riskFlag' => $this->heuristicRiskFlag($question, $answer),
            ];
        })->values()->all();

        $metrics = $this->buildMetrics($questions, $answers);

        $findings = [];
        if ($sections['findings'] ?? false) {
            $findings = $this->buildFindings($questions, $answers);
        }

        $timeline = [];
        if ($sections['timeline'] ?? false) {
            $timeline = $this->buildTimeline($instance);
        }

        return [
            'instance' => $instance,
            'rows' => $rows,
            'detailLevel' => $detail,
            'sections' => $sections,
            'meta' => [
                'completion_date' => $instance->submitted_at ?? $instance->finalized_at,
            ],
            'metrics' => $metrics,
            'findings' => $findings,
            'timeline' => $timeline,
            'documentSubtitle' => $detail->label(),
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function defaultSections(ExportDetailLevel $detail): array
    {
        return match ($detail) {
            ExportDetailLevel::Summary => [
                'metadata' => true,
                'metrics' => true,
                'responses' => false,
                'timeline' => false,
                'findings' => false,
                'toc' => false,
            ],
            ExportDetailLevel::Standard => [
                'metadata' => true,
                'metrics' => true,
                'responses' => true,
                'timeline' => false,
                'findings' => false,
                'toc' => false,
            ],
            ExportDetailLevel::Detailed => [
                'metadata' => true,
                'metrics' => true,
                'responses' => true,
                'timeline' => true,
                'findings' => true,
                'toc' => true,
            ],
            ExportDetailLevel::Executive => [
                'metadata' => true,
                'metrics' => true,
                'responses' => false,
                'timeline' => false,
                'findings' => true,
                'toc' => false,
            ],
        };
    }

    /**
     * Parse ?sections=metadata,metrics,responses from query string.
     *
     * @return array<string, bool>|null
     */
    /**
     * When provided, only listed sections are true; others false (explicit export scope).
     *
     * @return array<string, bool>|null
     */
    public static function sectionsFromQuery(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $keys = array_map('trim', explode(',', strtolower($raw)));
        $allowed = ['metadata', 'metrics', 'responses', 'timeline', 'findings', 'toc'];
        $out = [];
        foreach ($allowed as $k) {
            $out[$k] = in_array($k, $keys, true);
        }

        return $out;
    }

    /**
     * @param  Collection<int, ChecklistQuestion>  $questions
     * @param  Collection<int|string, ChecklistAnswer>  $answers
     * @return array<string, int|float>
     */
    private function buildMetrics(Collection $questions, Collection $answers): array
    {
        $requiredTotal = $questions->where('is_required', true)->count();
        $answered = 0;
        $requiredAnswered = 0;
        $na = 0;

        foreach ($questions as $q) {
            $a = $answers->get($q->id);
            if ($a === null) {
                continue;
            }
            $answered++;
            if ($a->is_not_applicable) {
                $na++;
            }
            if ($q->is_required && $this->validationOk($q, $a)) {
                $requiredAnswered++;
            }
        }

        return [
            'questions_total' => $questions->count(),
            'questions_answered' => $answered,
            'required_total' => $requiredTotal,
            'required_satisfied' => $requiredAnswered,
            'not_applicable' => $na,
            'coverage_pct' => $questions->count() > 0
                ? round(100 * $answered / $questions->count(), 1)
                : 0.0,
        ];
    }

    private function validationOk(ChecklistQuestion $question, ?ChecklistAnswer $answer): bool
    {
        if (! $question->is_required) {
            return true;
        }

        if ($answer === null) {
            return false;
        }

        if ($answer->is_not_applicable) {
            return true;
        }

        $text = $this->formatter->format($question, $answer, false);

        return trim($text) !== '' && $text !== '—';
    }

    /**
     * @param  Collection<int, ChecklistQuestion>  $questions
     * @param  Collection<int|string, ChecklistAnswer>  $answers
     * @return list<array{label: string, severity: string, detail: string}>
     */
    private function buildFindings(Collection $questions, Collection $answers): array
    {
        $out = [];

        foreach ($questions as $q) {
            $a = $answers->get($q->id);
            if ($a === null || $a->is_not_applicable) {
                continue;
            }

            $severity = null;
            $detail = '';

            if ($q->type === ChecklistQuestionType::Boolean && is_array($a->value) && array_key_exists('boolean', $a->value)) {
                if ($a->value['boolean'] === false) {
                    $severity = 'attention';
                    $detail = 'Recorded as No.';
                }
            }

            $choice = is_array($a->value) ? ($a->value['choice'] ?? null) : null;
            if (is_string($choice) && in_array($choice, ['gap', 'partial'], true)) {
                $severity = 'risk';
                $detail = 'Disposition indicates '.$choice.'.';
            }

            if ($severity !== null) {
                $out[] = [
                    'label' => $q->label,
                    'severity' => $severity,
                    'detail' => $detail,
                ];
            }
        }

        return $out;
    }

    private function heuristicRiskFlag(ChecklistQuestion $question, ?ChecklistAnswer $answer): ?string
    {
        if ($answer === null || $answer->is_not_applicable) {
            return null;
        }

        if ($question->type === ChecklistQuestionType::Boolean && is_array($answer->value) && ($answer->value['boolean'] ?? null) === false) {
            return 'attention';
        }

        $choice = is_array($answer->value) ? ($answer->value['choice'] ?? null) : null;
        if (is_string($choice) && in_array($choice, ['gap', 'partial'], true)) {
            return 'risk';
        }

        return null;
    }

    /**
     * @return list<array{label: string, at: string}>
     */
    private function buildTimeline(ChecklistInstance $instance): array
    {
        $events = [];

        if ($instance->created_at) {
            $events[] = ['label' => 'Instance created', 'at' => $instance->created_at->format('Y-m-d H:i:s T')];
        }
        if ($instance->started_at) {
            $events[] = ['label' => 'Audit started', 'at' => $instance->started_at->format('Y-m-d H:i:s T')];
        }
        if ($instance->submitted_at) {
            $events[] = ['label' => 'Submitted for review', 'at' => $instance->submitted_at->format('Y-m-d H:i:s T')];
        }
        if ($instance->finalized_at) {
            $events[] = ['label' => 'Finalized / approved', 'at' => $instance->finalized_at->format('Y-m-d H:i:s T')];
        }
        if ($instance->updated_at && $instance->status->value === 'rejected') {
            $events[] = ['label' => 'Last update (status: '.$instance->status->label().')', 'at' => $instance->updated_at->format('Y-m-d H:i:s T')];
        }

        return $events;
    }

    public function documentTitle(ChecklistInstance $instance, ExportDetailLevel $detail): string
    {
        $title = $instance->template?->name ?? $instance->public_id;
        $suffix = match ($detail) {
            ExportDetailLevel::Summary => 'Summary',
            ExportDetailLevel::Standard => 'Audit record',
            ExportDetailLevel::Detailed => 'Detailed audit record',
            ExportDetailLevel::Executive => 'Executive summary',
        };

        return $suffix.' · '.$title;
    }
}
