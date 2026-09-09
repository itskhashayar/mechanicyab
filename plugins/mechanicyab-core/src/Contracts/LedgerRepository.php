<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface LedgerRepository
{
    /** @param list<array{entry_key: string, account_code: string, direction: string, amount: int, currency: string, payment_id: int|null, metadata: array<string, mixed>}> $entries */
    public function post(array $entries): bool;
}
