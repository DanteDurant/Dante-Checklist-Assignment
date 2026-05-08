<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\ChecklistTemplates\Services\ChecklistQuestionService;
use App\Enums\ChecklistQuestionType;
use App\Http\Controllers\Controller;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ChecklistQuestionController extends Controller
{
    public function __construct(private readonly ChecklistQuestionService $service)
    {
    }

    public function store(Request $request, ChecklistTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'question_text' => ['required', 'string', 'max:5000'],
            'answer_type' => ['required', new Enum(ChecklistQuestionType::class)],
            'required' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $this->service->create($template, $data);

        return redirect()
            ->route('admin.templates.show', $template)
            ->with('status', 'Question added.');
    }

    public function destroy(ChecklistTemplate $template, ChecklistQuestion $question): RedirectResponse
    {
        abort_unless($question->checklist_template_id === $template->id, 404);

        $this->service->delete($question);

        return redirect()
            ->route('admin.templates.show', $template)
            ->with('status', 'Question deleted.');
    }
}

