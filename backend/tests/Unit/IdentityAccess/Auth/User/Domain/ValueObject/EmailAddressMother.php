<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use Tests\Unit\Shared\Mother\EmailMother;

final class EmailAddressMother
{
    public static function random(): EmailAddress
    {
        return new EmailAddress(EmailMother::random());
    }
}
