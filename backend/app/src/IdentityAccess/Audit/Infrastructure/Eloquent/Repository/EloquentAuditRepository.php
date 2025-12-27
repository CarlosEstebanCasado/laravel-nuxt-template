<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Infrastructure\Eloquent\Repository;

use App\Src\IdentityAccess\Audit\Domain\Collection\AuditCollection;
use App\Src\IdentityAccess\Audit\Domain\Entity\Audit as DomainAudit;
use App\Src\IdentityAccess\Audit\Domain\Repository\AuditRepository;
use App\Src\IdentityAccess\Audit\Domain\Response\AuditCollectionResponse;
use App\Src\Shared\Domain\Response\PaginationResponse;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
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

        $audits = $paginator
            ->map(function ($audit) {
                if (! $audit instanceof Audit) {
                    throw new InvalidArgumentException('Expected OwenIt audit model instance.');
                }

                $createdAt = $audit->getAttribute('created_at');
                $createdAtValue = $createdAt instanceof Carbon
                    ? new \DateTimeImmutable($createdAt->toDateTimeString())
                    : new \DateTimeImmutable();

                $oldValues = $audit->getAttribute('old_values');
                $newValues = $audit->getAttribute('new_values');

                return new DomainAudit(
                    id: $this->resolveAuditId($audit),
                    event: $this->stringOrFallback($audit->getAttribute('event')),
                    createdAt: $createdAtValue,
                    oldValues: is_array($oldValues) ? $this->normalizeAuditValues($oldValues) : null,
                    newValues: is_array($newValues) ? $this->normalizeAuditValues($newValues) : null,
                    ipAddress: $this->stringOrNull($audit->getAttribute('ip_address')),
                    userAgent: $this->stringOrNull($audit->getAttribute('user_agent')),
                    tags: $this->stringOrNull($audit->getAttribute('tags')),
                );
            })
            ->all();

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

    private function resolveAuditId(Audit $audit): int
    {
        $id = $audit->getKey();

        if (! is_int($id) && ! is_string($id)) {
            throw new InvalidArgumentException('Audit primary key must be string or int.');
        }

        if (! is_numeric($id)) {
            throw new InvalidArgumentException('Audit primary key must be numeric.');
        }

        return (int) $id;
    }

    /**
     * @param array<mixed, mixed> $values
     * @return array<string, mixed>
     */
    private function normalizeAuditValues(array $values): array
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return $normalized;
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private function stringOrFallback(mixed $value, string $fallback = ''): string
    {
        return is_string($value) ? $value : $fallback;
    }
}
