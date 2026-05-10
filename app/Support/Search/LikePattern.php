<?php

namespace App\Support\Search;

/**
 * Builds SQL LIKE patterns with wildcards in user input escaped.
 */
final class LikePattern
{
    public static function wrap(?string $term): ?string
    {
        $term = trim((string) $term);

        if ($term === '') {
            return null;
        }

        $escaped = addcslashes($term, '%_\\');

        return '%'.$escaped.'%';
    }
}
