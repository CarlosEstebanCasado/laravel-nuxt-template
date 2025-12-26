<?php

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use App\Src\IdentityAccess\Auth\User\UI\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->fresh()),
        ], 201);
    }
}
