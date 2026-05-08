<?php

namespace App\Http\Controllers\Web\Auditor;

use App\Application\Assessments\Services\ChecklistCompletionService;
use App\Http\Controllers\Controller;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChecklistInstanceController extends Controller
{
    public function __construct(private readonly ChecklistCompletionService $service)
    {
    }

    /**
     * Start a new checklist instance for the current auditor.
     */
    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['required', 'integer', 'exists:checklist_templates,id'],
        ]);

        /** @var ChecklistTemplate $template */
        $template = ChecklistTemplate::query()->findOrFail((int) $data['template_id']);

        $instance = $this->service->startInstance($template, $request->user());

        return redirect()
            ->route('auditor.instances.show', $instance)
            ->with('status', 'Checklist started.');
    }

    public function show(ChecklistInstance $instance): \Illuminate\View\View
    {
        $this->authorize('view', $instance);

        $instance->load(['template:id,name']);

        return view('auditor.instances.show', [
            'instance' => $instance,
        ]);
    }
}

