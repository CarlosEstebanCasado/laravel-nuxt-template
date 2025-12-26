<?php

namespace App\Src\IdentityAccess\Audit\Application\Converter;

use App\Src\IdentityAccess\Audit\Application\Response\AuditResponseItem;
use App\Src\IdentityAccess\Audit\Application\Response\GetAuditListUseCaseResponse;
use App\Src\IdentityAccess\Audit\Domain\Entity\Audit;
use App\Src\IdentityAccess\Audit\Domain\Response\AuditCollectionResponse;

final class AuditListConverter
{
    public function __construct(
        private readonly AuditResponseItemConverter $auditItemConverter
    ) {
    }

    public function toResponse(AuditCollectionResponse $collectionResponse): GetAuditListUseCaseResponse
    {
        $response = new GetAuditListUseCaseResponse();

        /** @var Audit $audit */
        foreach ($collectionResponse->items() as $audit) {
            $response->data[] = $this->auditItemConverter->toResponse($audit);
        }

        $pagination = $collectionResponse->pagination();
        if ($pagination !== null) {
            $response->meta = [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
            ];
        }

        return $response;
    }
}

