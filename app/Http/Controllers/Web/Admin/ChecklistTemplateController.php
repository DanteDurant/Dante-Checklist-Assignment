<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\ChecklistTemplates\Services\ChecklistQuestionService;
use App\Application\ChecklistTemplates\Services\ChecklistTemplateService;
use App\Enums\ChecklistTemplateStatus;
use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Support\QuestionTextNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class ChecklistTemplateController extends Controller
{
    public function __construct(
        private readonly ChecklistTemplateService $service,
        private readonly ChecklistQuestionService $questionService,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min((int) $request->integer('per_page', (int) config('list.default_per_page', 15)), (int) config('list.max_per_page', 100)));
        $search = $request->string('search')->toString();

        $templates = $this->service->paginate($perPage, $search);

        return view('admin.templates.index', [
            'templates' => $templates,
            'search' => $search,
        ]);
    }

    public function create(): View
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

    public function show(Request $request, ChecklistTemplate $template): View
    {
        $maxQuestions = (int) config('list.max_per_page_questions', 200);
        $perPage = max(1, min((int) $request->integer('per_page', (int) config('list.default_per_page', 15)), $maxQuestions));
        $search = $request->string('search')->toString();

        $template->loadCount('questions');

        $questions = $this->questionService->paginate($template, $perPage, $search);

        return view('admin.templates.show', [
            'template' => $template,
            'questions' => $questions,
            'search' => $search,
            'existing_question_signatures' => $template->questions()
                ->pluck('label')
                ->map(fn (string $label) => QuestionTextNormalizer::normalize($label))
                ->values()
                ->all(),
        ]);
    }

    public function edit(ChecklistTemplate $template): View
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
            ->with('status', 'Template archived.');
    }
}
