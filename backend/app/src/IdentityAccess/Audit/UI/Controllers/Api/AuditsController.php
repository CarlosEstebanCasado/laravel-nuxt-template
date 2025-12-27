<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\UI\Controllers\Api;

use App\Src\IdentityAccess\Audit\Application\Request\ListUserAuditsUseCaseRequest;
use App\Src\IdentityAccess\Audit\Application\UseCase\ListUserAuditsUseCase;
use App\Src\Shared\UI\Controllers\Controller;
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
        $user = $this->requireUser($request);

        $result = $this->useCase->execute(
            new ListUserAuditsUseCaseRequest(
                auditableType: $user::class,
                auditableId: $this->requireUserId($user),
                perPage: 10,
                page: (int) $request->integer('page', 1),
            )
        );

        return response()->json($result);
    }
}
