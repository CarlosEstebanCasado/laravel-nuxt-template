<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([], 204);
    }
}
