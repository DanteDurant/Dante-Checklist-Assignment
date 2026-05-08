<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|\Illuminate\View\View
    {
        if ($request->user()?->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()?->hasRole('auditor')) {
            return redirect()->route('auditor.dashboard');
        }

        return view('home');
    }
}

