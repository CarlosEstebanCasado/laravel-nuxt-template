<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Application\Response;

final class AuditResponseItem
{
    public function __construct(
        public int $id,
        public string $event,
        public string $created_at,
        /** @var array<string, mixed>|null */
        public ?array $old_values = null,
        /** @var array<string, mixed>|null */
        public ?array $new_values = null,
        public ?string $ip_address = null,
        public ?string $user_agent = null,
        public ?string $tags = null,
    ) {
    }
}

