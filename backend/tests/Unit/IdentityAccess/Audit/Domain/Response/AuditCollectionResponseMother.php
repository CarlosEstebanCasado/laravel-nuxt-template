<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Audit\Domain\Response;

use App\Src\IdentityAccess\Audit\Domain\Collection\AuditCollection;
use App\Src\IdentityAccess\Audit\Domain\Response\AuditCollectionResponse;
use App\Src\Shared\Domain\Response\PaginationResponse;
use Tests\Unit\IdentityAccess\Audit\Domain\Entity\AuditMother;
use Tests\Unit\Shared\Mother\IntegerMother;

final class AuditCollectionResponseMother
{
    public static function random(): AuditCollectionResponse
    {
        $items = [
            AuditMother::random(),
            AuditMother::random(),
        ];

        return new AuditCollectionResponse(
            items: new AuditCollection($items),
            pagination: new PaginationResponse(
                currentPage: IntegerMother::random(),
                perPage: IntegerMother::random(),
                total: IntegerMother::random(),
                lastPage: IntegerMother::random(),
            ),
        );
    }
}
