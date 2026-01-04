<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Collection;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\RecoveryCode;
use App\Src\Shared\Domain\Collection;

final class RecoveryCodeCollection extends Collection
{
    /**
     * @return list<string>
     */
    public function values(): array
    {
        $values = [];

        foreach ($this->items() as $code) {
            if (! $code instanceof RecoveryCode) {
                continue;
            }

            $values[] = $code->value();
        }

        return $values;
    }

    protected function type(): string
    {
        return RecoveryCode::class;
    }
}
