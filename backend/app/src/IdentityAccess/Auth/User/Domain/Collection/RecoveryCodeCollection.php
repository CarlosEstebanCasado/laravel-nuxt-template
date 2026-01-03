<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Collection;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\RecoveryCode;
use App\Src\Shared\Domain\Collection;

final class RecoveryCodeCollection extends Collection
{
    /**
     * @return string[]
     */
    public function values(): array
    {
        return $this->map(static fn (RecoveryCode $code) => $code->value());
    }

    protected function type(): string
    {
        return RecoveryCode::class;
    }
}
