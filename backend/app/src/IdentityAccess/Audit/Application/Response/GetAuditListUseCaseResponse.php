<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Application\Response;

final class GetAuditListUseCaseResponse
{
    /** @var array<int, AuditResponseItem> */
    public array $data = [];

    /**
     * @var array{current_page:int,last_page:int,per_page:int,total:int}|null
     */
    public ?array $meta = null;
}

