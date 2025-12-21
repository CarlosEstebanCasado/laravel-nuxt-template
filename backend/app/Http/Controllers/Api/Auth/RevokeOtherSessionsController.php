<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditEvent;
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

        AuditEvent::record(
            user: $request->user(),
            event: 'sessions_revoked',
            newValues: ['revoked' => $revoked]
        );

        return response()->json([
            'data' => [
                'revoked' => $revoked,
            ],
        ]);
    }
}


