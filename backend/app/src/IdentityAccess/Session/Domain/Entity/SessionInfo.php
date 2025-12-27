<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Domain\Entity;

final class SessionInfo
{
    public function __construct(
        private readonly string $id,
        private readonly ?string $ipAddress,
        private readonly ?string $userAgent,
        private readonly int $lastActivity,
        private readonly bool $isCurrent,
    ) {
    }

    public function markCurrent(): self
    {
        return new self(
            id: $this->id,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            lastActivity: $this->lastActivity,
            isCurrent: true,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function lastActivity(): int
    {
        return $this->lastActivity;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

}

