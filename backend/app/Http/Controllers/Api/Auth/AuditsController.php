<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $audits = Audit::query()
            ->where('auditable_type', $user::class)
            ->where('auditable_id', $user->getAuthIdentifier())
            ->orderByDesc('created_at')
            ->paginate(perPage: 10);

        return response()->json([
            'data' => $audits->items(),
            'meta' => [
                'current_page' => $audits->currentPage(),
                'last_page' => $audits->lastPage(),
                'per_page' => $audits->perPage(),
                'total' => $audits->total(),
            ],
        ]);
    }
}
