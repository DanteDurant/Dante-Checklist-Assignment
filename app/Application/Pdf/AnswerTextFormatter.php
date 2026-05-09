<?php

namespace App\Application\Pdf;

use App\Enums\ChecklistQuestionType;
use App\Models\ChecklistAnswer;
use App\Models\ChecklistQuestion;

final class AnswerTextFormatter
{
    public function format(ChecklistQuestion $question, ?ChecklistAnswer $answer, bool $appendNotes = true): string
    {
        if ($answer === null) {
            return '—';
        }

        if ($answer->is_not_applicable) {
            $base = 'Not applicable';
            if ($appendNotes && $answer->notes !== null && trim((string) $answer->notes) !== '') {
                $base .= ' — '.trim((string) $answer->notes);
            }

            return $base;
        }

        $stored = is_array($answer->value) ? $answer->value : [];
        $type = $question->type;

        $text = match ($type) {
            ChecklistQuestionType::Boolean => isset($stored['boolean'])
                ? (($stored['boolean'] ? 'Yes' : 'No'))
                : '—',

            ChecklistQuestionType::Number => array_key_exists('number', $stored) && $stored['number'] !== null
                ? (string) $stored['number']
                : '—',

            ChecklistQuestionType::Date => ! empty($stored['date'])
                ? (string) $stored['date']
                : '—',

            ChecklistQuestionType::DateTime => ! empty($stored['datetime'])
                ? (string) $stored['datetime']
                : '—',

            ChecklistQuestionType::Select,
            ChecklistQuestionType::Radio,
            ChecklistQuestionType::SingleSelect => $this->formatSingleChoice($question, $stored['choice'] ?? null),

            ChecklistQuestionType::Checkbox,
            ChecklistQuestionType::MultiSelect => $this->formatMultiChoice($question, $stored['choices'] ?? []),

            ChecklistQuestionType::Text,
            ChecklistQuestionType::Textarea,
            ChecklistQuestionType::Email,
            ChecklistQuestionType::Phone,
            ChecklistQuestionType::Url => ($stored['text'] ?? null) !== null && (string) $stored['text'] !== ''
                ? (string) $stored['text']
                : '—',

            default => $this->formatFallback($stored),
        };

        if ($appendNotes && $answer->notes !== null && trim((string) $answer->notes) !== '') {
            $text .= "\n\nNotes: ".trim((string) $answer->notes);
        }

        return $text;
    }

    /**
     * @param  array<int|string, mixed>  $stored
     */
    private function formatFallback(array $stored): string
    {
        if ($stored === []) {
            return '—';
        }

        $json = json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json !== false ? $json : '—';
    }

    private function formatSingleChoice(ChecklistQuestion $question, mixed $choice): string
    {
        if ($choice === null || $choice === '') {
            return '—';
        }

        $label = $this->labelForOptionValue($question, (string) $choice);

        return $label ?? (string) $choice;
    }

    /**
     * @param  array<int|string, mixed>  $choices
     */
    private function formatMultiChoice(ChecklistQuestion $question, array $choices): string
    {
        $flat = array_values(array_filter(array_map('strval', $choices), fn ($v) => $v !== ''));

        if ($flat === []) {
            return '—';
        }

        $labels = array_map(function (string $value) use ($question) {
            return $this->labelForOptionValue($question, $value) ?? $value;
        }, $flat);

        return implode(', ', $labels);
    }

    private function labelForOptionValue(ChecklistQuestion $question, string $value): ?string
    {
        $raw = $question->options ?? [];

        foreach ($raw as $row) {
            if (is_array($row) && array_key_exists('value', $row)) {
                if ((string) $row['value'] === $value) {
                    return isset($row['label']) ? (string) $row['label'] : (string) $row['value'];
                }
            }
        }

        return null;
    }
}
