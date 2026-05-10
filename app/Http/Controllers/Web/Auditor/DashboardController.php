<?php

namespace App\Http\Controllers\Web\Auditor;

use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $auditorId = $request->user()->id;

        $perPage = max(1, min((int) $request->integer('per_page', (int) config('list.default_per_page', 15)), (int) config('list.max_per_page', 100)));
        $search = $request->string('search')->toString();
        $templateSearch = $request->string('template_search')->toString();

        $instances = ChecklistInstance::query()
            ->where('auditor_id', $auditorId)
            ->when(trim($search) !== '', fn ($q) => $q->search($search))
            ->with(['template:id,name'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $publishedTemplates = ChecklistTemplate::query()
            ->where('status', 'published')
            ->when(trim($templateSearch) !== '', fn ($q) => $q->search($templateSearch))
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name']);

        return view('auditor.dashboard', [
            'instances' => $instances,
            'publishedTemplates' => $publishedTemplates,
            'search' => $search,
            'templateSearch' => $templateSearch,
        ]);
    }
}
