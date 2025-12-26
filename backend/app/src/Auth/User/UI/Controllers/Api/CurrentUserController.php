<?php

namespace App\BoundedContext\Auth\User\UI\Controllers\Api;

use App\BoundedContext\Auth\User\UI\Resources\UserResource;
use App\BoundedContext\Shared\Shared\UI\Controllers\Controller;
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
