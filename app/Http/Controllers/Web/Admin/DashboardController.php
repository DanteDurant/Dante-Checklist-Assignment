<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        $templates = ChecklistTemplate::query()
            ->withCount('questions')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'templates' => $templates,
        ]);
    }
}

