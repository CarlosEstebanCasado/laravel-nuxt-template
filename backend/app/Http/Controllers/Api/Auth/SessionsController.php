<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->getAuthIdentifier();
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get([
                'id',
                'ip_address',
                'user_agent',
                'last_activity',
            ])
            ->map(function ($row) use ($currentSessionId) {
                return [
                    'id' => $row->id,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => (int) $row->last_activity,
                    'is_current' => $row->id === $currentSessionId,
                ];
            })
            ->values();

        return response()->json([
            'data' => $sessions,
        ]);
    }
}


