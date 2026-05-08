<?php

namespace App\Http\Controllers\Web\Auditor;

use App\Enums\ChecklistInstanceStatus;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;

class DashboardController
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        $auditorId = $request->user()->id;

        $instances = ChecklistInstance::query()
            ->where('auditor_id', $auditorId)
            ->with(['template:id,name'])
            ->latest('id')
            ->limit(25)
            ->get();

        $publishedTemplates = ChecklistTemplate::query()
            ->where('status', 'published')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('auditor.dashboard', [
            'instances' => $instances,
            'publishedTemplates' => $publishedTemplates,
        ]);
    }
}

