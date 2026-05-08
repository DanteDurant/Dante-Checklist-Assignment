<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditorController extends Controller
{
    public function ping(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'auditor ok',
            'user_id' => $request->user()?->id,
        ]);
    }
}

