<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\ValueObject;

use InvalidArgumentException;

final class RecoveryCode
{
    public function __construct(
        private readonly string $value
    ) {
        if ($value === '') {
            throw new InvalidArgumentException('Recovery code cannot be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
