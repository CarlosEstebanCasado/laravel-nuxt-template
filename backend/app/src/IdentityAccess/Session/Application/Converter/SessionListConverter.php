<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\Converter;

use App\Src\IdentityAccess\Session\Application\Response\GetSessionListUseCaseResponse;
use App\Src\IdentityAccess\Session\Domain\Response\SessionCollectionResponse;

final class SessionListConverter
{
    public function __construct(
        private readonly SessionResponseItemConverter $itemConverter
    ) {
    }

    public function toResponse(SessionCollectionResponse $collectionResponse, string $currentSessionId): GetSessionListUseCaseResponse
    {
        $response = new GetSessionListUseCaseResponse();

        foreach ($collectionResponse->items() as $session) {
            $normalizedSession = $session->id() === $currentSessionId
                ? $session->markCurrent()
                : $session;

            $response->data[] = $this->itemConverter->toResponse($normalizedSession, $currentSessionId);
        }

        return $response;
    }
}
