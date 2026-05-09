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
            'options_text' => ['nullable', 'string', 'max:10000'],
        ]);

        $options = null;
        if (!empty($data['options_text'])) {
            $lines = preg_split("/\r\n|\n|\r/", $data['options_text']) ?: [];
            $lines = array_values(array_filter(array_map('trim', $lines), fn ($v) => $v !== ''));

            if (!empty($lines)) {
                $options = array_map(fn ($v) => ['value' => $v, 'label' => $v], $lines);
            }
        }

        unset($data['options_text']);
        $data['options'] = $options;

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

