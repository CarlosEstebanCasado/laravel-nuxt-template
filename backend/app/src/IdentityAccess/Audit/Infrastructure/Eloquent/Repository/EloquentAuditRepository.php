<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Infrastructure\Eloquent\Repository;

use App\Src\IdentityAccess\Audit\Domain\Collection\AuditCollection;
use App\Src\IdentityAccess\Audit\Domain\Entity\Audit as DomainAudit;
use App\Src\IdentityAccess\Audit\Domain\Repository\AuditRepository;
use App\Src\IdentityAccess\Audit\Domain\Response\AuditCollectionResponse;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditEvent;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditId;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditTags;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditValues;
use App\Src\Shared\Domain\Response\PaginationResponse;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;
use App\Src\Shared\Domain\ValueObject\IpAddress;
use App\Src\Shared\Domain\ValueObject\UserAgent;
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
                    : new \DateTimeImmutable;

                $oldValues = $audit->getAttribute('old_values');
                $newValues = $audit->getAttribute('new_values');

                return new DomainAudit(
                    id: new AuditId($this->resolveAuditId($audit)),
                    event: new AuditEvent($this->stringOrFallback($audit->getAttribute('event'), 'unknown')),
                    createdAt: new DateTimeValue($createdAtValue),
                    oldValues: is_array($oldValues) ? new AuditValues($this->normalizeAuditValues($oldValues)) : null,
                    newValues: is_array($newValues) ? new AuditValues($this->normalizeAuditValues($newValues)) : null,
                    ipAddress: $this->stringToIpAddress($audit->getAttribute('ip_address')),
                    userAgent: $this->stringToUserAgent($audit->getAttribute('user_agent')),
                    tags: $this->stringToTags($audit->getAttribute('tags')),
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
     * @param  array<mixed, mixed>  $values
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

    private function stringToIpAddress(mixed $value): ?IpAddress
    {
        return is_string($value) && $value !== '' ? new IpAddress($value) : null;
    }

    private function stringToUserAgent(mixed $value): ?UserAgent
    {
        return is_string($value) && $value !== '' ? new UserAgent($value) : null;
    }

    private function stringToTags(mixed $value): ?AuditTags
    {
        return is_string($value) && $value !== '' ? new AuditTags($value) : null;
    }

    private function stringOrFallback(mixed $value, string $fallback = ''): string
    {
        return is_string($value) ? $value : $fallback;
    }
}
