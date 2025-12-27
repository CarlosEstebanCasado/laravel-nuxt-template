<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\Entity;

final class Audit
{
    /**
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    public function __construct(
        private readonly int $id,
        private readonly string $event,
        private readonly \DateTimeImmutable $createdAt,
        private readonly ?array $oldValues,
        private readonly ?array $newValues,
        private readonly ?string $ipAddress,
        private readonly ?string $userAgent,
        private readonly ?string $tags,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function event(): string
    {
        return $this->event;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function oldValues(): ?array
    {
        return $this->oldValues;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function newValues(): ?array
    {
        return $this->newValues;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function tags(): ?string
    {
        return $this->tags;
    }
}

