<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\Reporting\Queries\ChecklistInstanceReportQuery;
use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function __construct(private readonly ChecklistInstanceReportQuery $query) {}

    public function checklistInstances(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'template_id' => ['nullable', 'integer', 'exists:checklist_templates,id'],
            'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'q' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (empty($filters['q']) && ! empty($filters['search'])) {
            $filters['q'] = $filters['search'];
        }
        unset($filters['search']);

        $templates = ChecklistTemplate::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $auditors = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'auditor'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $results = $this->query->paginate($filters);

        return view('admin.reports.checklist-instances', [
            'filters' => $filters,
            'templates' => $templates,
            'auditors' => $auditors,
            'results' => $results,
        ]);
    }
}
