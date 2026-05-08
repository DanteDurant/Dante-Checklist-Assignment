<?php

namespace App\Http\Controllers\Web\Auditor;

use App\Models\ChecklistInstance;
use Illuminate\Http\Request;

class DashboardController
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        $instances = ChecklistInstance::query()
            ->where('auditor_id', $request->user()->id)
            ->latest('id')
            ->limit(10)
            ->get();

        return view('auditor.dashboard', [
            'instances' => $instances,
        ]);
    }
}

