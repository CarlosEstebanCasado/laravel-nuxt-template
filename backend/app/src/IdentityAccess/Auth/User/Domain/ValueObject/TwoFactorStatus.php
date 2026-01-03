<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\ValueObject;

use App\Src\Shared\Domain\ValueObject\DateTimeValue;

final class TwoFactorStatus
{
    public function __construct(
        private bool $enabled,
        private bool $confirmed
    ) {
    }

    public static function fromState(?string $secret, ?DateTimeValue $confirmedAt): self
    {
        $enabled = $secret !== null && $secret !== '';
        $confirmed = $confirmedAt !== null;

        return new self($enabled, $confirmed);
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function confirmed(): bool
    {
        return $this->confirmed;
    }
}
