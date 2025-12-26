<?php

namespace App\BoundedContext\Audit\Audit\UI\Controllers\Api;

use App\BoundedContext\Audit\Audit\Application\Request\ListUserAuditsUseCaseRequest;
use App\BoundedContext\Audit\Audit\Application\UseCase\ListUserAuditsUseCase;
use App\BoundedContext\Shared\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditsController extends Controller
{
    public function __construct(
        private readonly ListUserAuditsUseCase $useCase
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->useCase->execute(new ListUserAuditsUseCaseRequest(
            auditableType: $user::class,
            auditableId: (int) $user->getAuthIdentifier(),
            perPage: 10,
            page: (int) $request->integer('page', 1),
        ));

        return response()->json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }
}
