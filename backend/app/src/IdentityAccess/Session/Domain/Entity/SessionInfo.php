<?php

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

    /**
     * @return array{id:string,ip_address:?string,user_agent:?string,last_activity:int,is_current:bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'last_activity' => $this->lastActivity,
            'is_current' => $this->isCurrent,
        ];
    }
}


