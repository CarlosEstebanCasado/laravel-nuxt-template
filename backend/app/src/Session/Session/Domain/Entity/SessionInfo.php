<?php

namespace App\Src\Session\Session\Domain\Entity;

final class SessionInfo
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly int $lastActivity,
        public readonly bool $isCurrent,
    ) {
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




