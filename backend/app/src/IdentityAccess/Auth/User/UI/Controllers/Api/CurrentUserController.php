<?php

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\IdentityAccess\Auth\User\UI\Resources\UserResource;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    public function __invoke(Request $request): UserResource
    {
        return new UserResource(
            $request->user()->fresh()
        );
    }
}
