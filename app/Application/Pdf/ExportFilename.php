<?php

namespace App\Application\Pdf;

use App\Enums\ExportDetailLevel;

final class ExportFilename
{
    /**
     * Safe, descriptive PDF filenames for downloads.
     */
    public static function build(string $slug, ExportDetailLevel $detail, ?string $datePrefix = null): string
    {
        $date = $datePrefix ?? now()->format('Y-m-d');
        $slugPart = self::slug($slug);
        $detailPart = $detail->value;

        return "{$slugPart}-{$detailPart}-{$date}";
    }

    private static function slug(string $value): string
    {
        $value = preg_replace('/[^\p{L}\p{N}\s\-]+/u', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', '-', trim($value)) ?? $value;
        $value = strtolower($value);

        if ($value === '') {
            return 'export';
        }

        return mb_substr($value, 0, 80);
    }
}
