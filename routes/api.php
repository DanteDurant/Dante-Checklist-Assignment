<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuditorController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChecklistQuestionController;
use App\Http\Controllers\Api\V1\ChecklistTemplateController;

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
        });

        Route::middleware('role:auditor')->group(function () {
            Route::get('/auditor/ping', [AuditorController::class, 'ping']);
        });
    });
});
