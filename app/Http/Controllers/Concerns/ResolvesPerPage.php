<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ResolvesPerPage
{
    protected function resolvePerPage(Request $request, ?int $default = null, ?int $max = null): int
    {
        $default ??= (int) config('list.default_per_page', 15);
        $max ??= (int) config('list.max_per_page', 100);

        return max(1, min((int) $request->integer('per_page', $default), $max));
    }
}
