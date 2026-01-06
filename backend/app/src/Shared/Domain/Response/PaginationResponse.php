<?php

declare(strict_types=1);

namespace App\Src\Shared\Domain\Response;

final class PaginationResponse
{
    public function __construct(
        private readonly int $currentPage,
        private readonly int $lastPage,
        private readonly int $perPage,
        private readonly int $total,
    ) {}

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function lastPage(): int
    {
        return $this->lastPage;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function total(): int
    {
        return $this->total;
    }
}
