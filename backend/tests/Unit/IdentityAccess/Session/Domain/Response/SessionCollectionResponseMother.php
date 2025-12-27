<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Session\Domain\Response;

use App\Src\IdentityAccess\Session\Domain\Collection\SessionCollection;
use App\Src\IdentityAccess\Session\Domain\Response\SessionCollectionResponse;
use Tests\Unit\IdentityAccess\Session\Domain\Entity\SessionInfoMother;

final class SessionCollectionResponseMother
{
    public static function random(): SessionCollectionResponse
    {
        return new SessionCollectionResponse(
            items: new SessionCollection([
                SessionInfoMother::random(),
                SessionInfoMother::random(),
            ])
        );
    }
}
