<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

final class SearchRequest
{
    /** @param array<string, mixed> $filters */
    public function __construct(
        public readonly array $filters = [],
        public readonly int $page = 1,
        public readonly int $perPage = 20,
        public readonly string $query = '',
    ) {
        if ($this->page < 1 || $this->perPage < 1 || $this->perPage > 100) {
            throw new \InvalidArgumentException('Invalid pagination.');
        }
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
