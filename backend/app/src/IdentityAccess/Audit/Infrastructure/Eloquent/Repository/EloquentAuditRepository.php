<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Infrastructure\Eloquent\Repository;

use App\Src\IdentityAccess\Audit\Domain\Collection\AuditCollection;
use App\Src\IdentityAccess\Audit\Domain\Entity\Audit as DomainAudit;
use App\Src\IdentityAccess\Audit\Domain\Repository\AuditRepository;
use App\Src\IdentityAccess\Audit\Domain\Response\AuditCollectionResponse;
use App\Src\Shared\Domain\Response\PaginationResponse;
use Illuminate\Support\Carbon;
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
            $createdAt = $audit->getAttribute('created_at');
            $createdAtValue = $createdAt instanceof Carbon
                ? new \DateTimeImmutable($createdAt->toDateTimeString())
                : new \DateTimeImmutable();

            $oldValues = $audit->getAttribute('old_values');
            $newValues = $audit->getAttribute('new_values');

            return new DomainAudit(
                id: (int) $audit->getKey(),
                event: (string) ($audit->getAttribute('event') ?? ''),
                createdAt: $createdAtValue,
                oldValues: is_array($oldValues) ? $oldValues : null,
                newValues: is_array($newValues) ? $newValues : null,
                ipAddress: $audit->getAttribute('ip_address'),
                userAgent: $audit->getAttribute('user_agent'),
                tags: $audit->getAttribute('tags'),
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
