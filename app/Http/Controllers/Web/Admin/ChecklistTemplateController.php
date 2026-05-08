<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\ChecklistTemplates\Services\ChecklistTemplateService;
use App\Enums\ChecklistTemplateStatus;
use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ChecklistTemplateController extends Controller
{
    public function __construct(private readonly ChecklistTemplateService $service)
    {
    }

    public function index(Request $request): \Illuminate\View\View
    {
        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));

        $templates = $this->service->paginate($perPage);

        return view('admin.templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        return view('admin.templates.create', [
            'statuses' => ChecklistTemplateStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', new Enum(ChecklistTemplateStatus::class)],
        ]);

        $template = $this->service->create($data, $request->user());

        return redirect()
            ->route('admin.templates.show', $template)
            ->with('status', 'Template created.');
    }

    public function show(ChecklistTemplate $template): \Illuminate\View\View
    {
        $template->load(['questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return view('admin.templates.show', [
            'template' => $template,
        ]);
    }

    public function edit(ChecklistTemplate $template): \Illuminate\View\View
    {
        return view('admin.templates.edit', [
            'template' => $template,
            'statuses' => ChecklistTemplateStatus::cases(),
        ]);
    }

    public function update(Request $request, ChecklistTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', new Enum(ChecklistTemplateStatus::class)],
        ]);

        $template = $this->service->update($template, $data);

        return redirect()
            ->route('admin.templates.show', $template)
            ->with('status', 'Template updated.');
    }

    public function destroy(ChecklistTemplate $template): RedirectResponse
    {
        $this->service->delete($template);

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Template deleted.');
    }
}

