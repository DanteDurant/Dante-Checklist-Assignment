<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuditorController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChecklistQuestionController;
use App\Http\Controllers\Api\V1\ChecklistTemplateController;
use App\Http\Controllers\Api\V1\Auditor\ChecklistInstanceController;
use App\Http\Controllers\Api\V1\Reports\ChecklistInstanceReportController;

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
