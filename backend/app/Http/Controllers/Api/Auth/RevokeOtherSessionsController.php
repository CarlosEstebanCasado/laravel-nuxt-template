<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevokeOtherSessionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->getAuthIdentifier();
        $currentSessionId = $request->session()->getId();

        $revoked = DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return response()->json([
            'data' => [
                'revoked' => $revoked,
            ],
        ]);
    }
}


