<?php

namespace App\Support;

final class QuestionTextNormalizer
{
    /**
     * Normalize question text for duplicate comparison (trim, collapse whitespace, case-insensitive).
     */
    public static function normalize(string $text): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($text));

        return mb_strtolower((string) $collapsed, 'UTF-8');
    }
}
