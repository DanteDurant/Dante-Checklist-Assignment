<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        $totalTemplates = ChecklistTemplate::query()->count();

        $totalAuditsCompleted = ChecklistInstance::query()
            ->whereIn('status', ['submitted', 'approved'])
            ->whereNotNull('submitted_at')
            ->count();

        $totalAuditors = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'auditor'))
            ->count();

        $templates = ChecklistTemplate::query()
            ->withCount('questions')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'totalTemplates' => $totalTemplates,
            'totalAuditsCompleted' => $totalAuditsCompleted,
            'totalAuditors' => $totalAuditors,
            'templates' => $templates,
        ]);
    }
}

