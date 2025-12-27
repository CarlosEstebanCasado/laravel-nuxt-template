<?php

namespace App\Src\IdentityAccess\Audit\Infrastructure\Eloquent\Repository;

use App\Src\IdentityAccess\Audit\Domain\Collection\AuditCollection;
use App\Src\IdentityAccess\Audit\Domain\Entity\Audit as DomainAudit;
use App\Src\IdentityAccess\Audit\Domain\Repository\AuditRepository;
use App\Src\IdentityAccess\Audit\Domain\Response\AuditCollectionResponse;
use App\Src\Shared\Domain\Response\PaginationResponse;
use OwenIt\Auditing\Models\Audit;

final class EloquentAuditRepository implements AuditRepository
{
    public function paginateForAuditable(
        string $auditableType,
        int $auditableId,
        int $perPage,
        int $page
    ): AuditCollectionResponse {
        $paginator = Audit::query()
            ->where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $audits = $paginator->map(function (Audit $audit) {
            $createdAt = $audit->created_at
                ? new \DateTimeImmutable($audit->created_at->toDateTimeString())
                : new \DateTimeImmutable();

            return new DomainAudit(
                id: (int) $audit->getKey(),
                event: (string) $audit->event,
                createdAt: $createdAt,
                oldValues: $audit->old_values ? (array) $audit->old_values : null,
                newValues: $audit->new_values ? (array) $audit->new_values : null,
                ipAddress: $audit->ip_address,
                userAgent: $audit->user_agent,
                tags: $audit->tags,
            );
        })->all();

        return new AuditCollectionResponse(
            new AuditCollection($audits),
            new PaginationResponse(
                currentPage: $paginator->currentPage(),
                lastPage: $paginator->lastPage(),
                perPage: $paginator->perPage(),
                total: $paginator->total(),
            ),
        );
    }
}
