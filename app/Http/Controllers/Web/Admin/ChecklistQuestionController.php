<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\ChecklistTemplates\Services\ChecklistQuestionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\StoreChecklistQuestionRequest;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Http\RedirectResponse;

class ChecklistQuestionController extends Controller
{
    public function __construct(private readonly ChecklistQuestionService $service) {}

    public function store(StoreChecklistQuestionRequest $request, ChecklistTemplate $template): RedirectResponse
    {
        $data = $request->validated();

        $options = null;
        if (! empty($data['options_text'])) {
            $lines = preg_split("/\r\n|\n|\r/", $data['options_text']) ?: [];
            $lines = array_values(array_filter(array_map('trim', $lines), fn ($v) => $v !== ''));

            if (! empty($lines)) {
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
