<?php

namespace App\Src\IdentityAccess\Session\Application\Response;

final class SessionResponseItem
{
    public function __construct(
        public string $id,
        public ?string $ip_address,
        public ?string $user_agent,
        public int $last_activity,
        public bool $is_current
    ) {
    }
}

