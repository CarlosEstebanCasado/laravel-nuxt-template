<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\UseCase;

use App\Src\IdentityAccess\Session\Application\Converter\SessionListConverter;
use App\Src\IdentityAccess\Session\Application\Request\ListUserSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\Response\GetSessionListUseCaseResponse;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;

final class ListUserSessionsUseCase
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly SessionListConverter $sessionListConverter
    ) {}

    public function execute(ListUserSessionsUseCaseRequest $request): GetSessionListUseCaseResponse
    {
        $collection = $this->sessionRepository->listForUser($request->userId);

        return $this->sessionListConverter->toResponse($collection, $request->currentSessionId);
    }
}
