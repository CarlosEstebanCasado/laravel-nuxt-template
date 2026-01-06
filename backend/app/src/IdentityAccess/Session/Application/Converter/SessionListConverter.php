<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\Converter;

use App\Src\IdentityAccess\Session\Application\Response\GetSessionListUseCaseResponse;
use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\Response\SessionCollectionResponse;
use App\Src\IdentityAccess\Session\Domain\Service\SessionInfoCurrentMarker;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionId;

final class SessionListConverter
{
    public function __construct(
        private readonly SessionResponseItemConverter $sessionResponseItemConverter,
        private readonly SessionInfoCurrentMarker $sessionInfoCurrentMarker
    ) {}

    public function toResponse(SessionCollectionResponse $collectionResponse, string $currentSessionId): GetSessionListUseCaseResponse
    {
        $response = new GetSessionListUseCaseResponse;
        $currentId = new SessionId($currentSessionId);

        foreach ($collectionResponse->items() as $session) {
            if (! $session instanceof SessionInfo) {
                continue;
            }

            $normalizedSession = $session->id()->equals($currentId)
                ? $this->sessionInfoCurrentMarker->mark($session)
                : $session;

            $response->data[] = $this->sessionResponseItemConverter->toResponse($normalizedSession, $currentId);
        }

        return $response;
    }
}
