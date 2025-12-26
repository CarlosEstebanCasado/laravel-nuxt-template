<?php

namespace App\BoundedContext\Auth\User\UI\Responses;

use App\BoundedContext\Auth\User\UI\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->fresh()),
        ]);
    }
}
