<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Auth\Services\TokenAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly TokenAuthService $authService)
    {
    }

    public function login(LoginRequest $request): AuthTokenResource
    {
        $data = $this->authService->login(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            tokenName: $request->string('device_name')->toString() ?: 'api',
        );

        return new AuthTokenResource($data);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out']);
    }
}

