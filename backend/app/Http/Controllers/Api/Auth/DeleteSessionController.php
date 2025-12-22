<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteSessionController extends Controller
{
    public function __invoke(Request $request, string $id): JsonResponse
    {
        if (! $request->hasSession()) {
            return response()->json([
                'message' => 'Session store is not available for this request.',
            ], 422);
        }

        $userId = $request->user()->getAuthIdentifier();
        $currentSessionId = $request->session()->getId();

        if ($id === $currentSessionId) {
            return response()->json([
                'message' => 'You cannot revoke the current session.',
            ], 422);
        }

        $deleted = DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'message' => 'Session not found.',
            ], 404);
        }

        AuditEvent::record(
            user: $request->user(),
            event: 'session_revoked',
            newValues: ['session_id' => $id]
        );

        return response()->json(status: 204);
    }
}


