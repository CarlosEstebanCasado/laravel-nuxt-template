<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\Converter;

use App\Src\IdentityAccess\Session\Application\Response\GetSessionListUseCaseResponse;
use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\Response\SessionCollectionResponse;

final class SessionListConverter
{
    public function __construct(
        private readonly SessionResponseItemConverter $sessionResponseItemConverter
    ) {
    }

    public function toResponse(SessionCollectionResponse $collectionResponse, string $currentSessionId): GetSessionListUseCaseResponse
    {
        $response = new GetSessionListUseCaseResponse();

        foreach ($collectionResponse->items() as $session) {
            if (! $session instanceof SessionInfo) {
                continue;
            }

            $normalizedSession = $session->id() === $currentSessionId
                ? $session->markCurrent()
                : $session;

            $response->data[] = $this->sessionResponseItemConverter->toResponse($normalizedSession, $currentSessionId);
        }

        return $response;
    }
}
