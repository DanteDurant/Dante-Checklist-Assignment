<?php

namespace App\Http\Controllers\Api;

use App\Application\Auth\Services\TokenAuthService;
use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly TokenAuthService $authService)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            tokenName: $request->string('device_name')->toString() ?: 'api',
        );

        return $this->success(
            data: (new AuthTokenResource($data))->resolve(),
            message: 'Logged in',
            status: 200,
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out', 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values(),
        ], 'OK', 200);
    }
}

