<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\ChecklistTemplateController as AdminChecklistTemplateController;
use App\Http\Controllers\Web\Admin\ChecklistQuestionController as AdminChecklistQuestionController;
use App\Http\Controllers\Web\Admin\ReportsController as AdminReportsController;
use App\Http\Controllers\Web\Auditor\DashboardController as AuditorDashboardController;
use App\Http\Controllers\Web\Auditor\ChecklistInstanceController as AuditorChecklistInstanceController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\HomeController;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    Route::resource('templates', AdminChecklistTemplateController::class)
        ->parameters(['templates' => 'template'])
        ->names('templates');

    Route::post('/templates/{template}/questions', [AdminChecklistQuestionController::class, 'store'])
        ->name('templates.questions.store');

    Route::delete('/templates/{template}/questions/{question}', [AdminChecklistQuestionController::class, 'destroy'])
        ->name('templates.questions.destroy');

    Route::get('/reports/checklist-instances', [AdminReportsController::class, 'checklistInstances'])
        ->name('reports.checklist_instances');
});

Route::middleware(['auth', 'role:auditor'])->prefix('auditor')->name('auditor.')->group(function () {
    Route::get('/dashboard', AuditorDashboardController::class)->name('dashboard');

    Route::get('/start', [AuditorChecklistInstanceController::class, 'start'])->name('start');
    Route::get('/instances/{instance}', [AuditorChecklistInstanceController::class, 'show'])->name('instances.show');
    Route::post('/instances/{instance}/draft', [AuditorChecklistInstanceController::class, 'saveDraft'])->name('instances.draft');
    Route::post('/instances/{instance}/submit', [AuditorChecklistInstanceController::class, 'submit'])->name('instances.submit');
});
