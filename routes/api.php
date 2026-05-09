<?php

use App\Http\Controllers\Api\AuthController as PublicAuthController;
use App\Http\Controllers\Api\ChecklistsController as PublicChecklistsController;
use App\Http\Controllers\Api\PdfExportsController;
use App\Http\Controllers\Api\QuestionsController as PublicQuestionsController;
use App\Http\Controllers\Api\TemplatesController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\Auditor\ChecklistInstanceController;
use App\Http\Controllers\Api\V1\AuditorController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChecklistQuestionController;
use App\Http\Controllers\Api\V1\ChecklistTemplateController;
use App\Http\Controllers\Api\V1\Reports\ChecklistInstanceReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/me', function (Request $request) {
            $user = $request->user();

            return response()->json([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->values(),
                ],
            ]);
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/ping', [AdminController::class, 'ping']);

            Route::apiResource('checklist-templates', ChecklistTemplateController::class)
                ->parameters(['checklist-templates' => 'template']);

            Route::apiResource('checklist-templates.questions', ChecklistQuestionController::class)
                ->shallow()
                ->parameters([
                    'checklist-templates' => 'template',
                    'questions' => 'question',
                ]);

            Route::get('/admin/reports/checklist-instances', [ChecklistInstanceReportController::class, 'index']);
        });

        Route::middleware('role:auditor')->group(function () {
            Route::get('/auditor/ping', [AuditorController::class, 'ping']);

            Route::get('/auditor/checklist-instances', [ChecklistInstanceController::class, 'index']);
            Route::post('/auditor/checklist-instances', [ChecklistInstanceController::class, 'store']);
            Route::get('/auditor/checklist-instances/{instance}', [ChecklistInstanceController::class, 'show']);
            Route::put('/auditor/checklist-instances/{instance}/answers', [ChecklistInstanceController::class, 'saveProgress']);
            Route::post('/auditor/checklist-instances/{instance}/complete', [ChecklistInstanceController::class, 'complete']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Public External API (stable /api/*)
|--------------------------------------------------------------------------
*/
Route::post('/login', [PublicAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [PublicAuthController::class, 'logout']);
    Route::get('/me', [PublicAuthController::class, 'me']);

    // Admin resources
    Route::middleware('role:admin')->group(function () {
        Route::get('/templates', [TemplatesController::class, 'index']);
        Route::post('/templates', [TemplatesController::class, 'store']);
        Route::get('/templates/{template}', [TemplatesController::class, 'show']);
        Route::put('/templates/{template}', [TemplatesController::class, 'update']);
        Route::delete('/templates/{template}', [TemplatesController::class, 'destroy']);

        Route::post('/templates/{template}/questions', [PublicQuestionsController::class, 'store']);
        Route::put('/questions/{question}', [PublicQuestionsController::class, 'update']);
        Route::delete('/questions/{question}', [PublicQuestionsController::class, 'destroy']);

        Route::get('/reports', [PublicChecklistsController::class, 'reports']);
        Route::get('/reports/export-pdf', [PdfExportsController::class, 'checklistReport']);
        Route::get('/reports/compliance-snapshot/export-pdf', [PdfExportsController::class, 'complianceSnapshot']);
        Route::get('/reports/auditor-activity/export-pdf', [PdfExportsController::class, 'auditorActivityReport']);
        Route::get('/templates/{template}/export-pdf', [PdfExportsController::class, 'checklistTemplate']);
    });

    Route::middleware('role:admin|auditor')->group(function () {
        Route::get('/checklists/{checklist}/export-pdf', [PdfExportsController::class, 'checklistInstance']);
    });

    // Auditor resources
    Route::middleware('role:auditor')->group(function () {
        Route::get('/checklists', [PublicChecklistsController::class, 'index']);
        Route::post('/checklists/start/{template}', [PublicChecklistsController::class, 'start']);
        Route::get('/checklists/{checklist}', [PublicChecklistsController::class, 'show']);
        Route::put('/checklists/{checklist}/save-draft', [PublicChecklistsController::class, 'saveDraft']);
        Route::put('/checklists/{checklist}/complete', [PublicChecklistsController::class, 'complete']);
    });
});
