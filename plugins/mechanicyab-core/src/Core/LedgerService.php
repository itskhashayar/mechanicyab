<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\LedgerRepository;

final class LedgerService
{
    public function __construct(private readonly LedgerRepository $repository) {}
    /** @param list<array{entry_key: string, account_code: string, direction: string, amount: int, currency?: string, payment_id?: int|null, metadata?: array<string, mixed>}> $entries */
    public function post(array $entries): bool
    {
        if (count($entries) < 2) { throw new \InvalidArgumentException('A ledger posting requires at least two entries.'); }
        $debit = 0; $credit = 0; $currency = null;
        foreach ($entries as $entry) { $amount=(int)$entry['amount']; if ($amount <= 0 || !in_array($entry['direction'], ['debit','credit'], true) || ($currency !== null && $currency !== ($entry['currency']??'IRR'))) { throw new \InvalidArgumentException('Invalid or unbalanced ledger entry.'); } $currency ??= $entry['currency']??'IRR'; if ($entry['direction']==='debit') {$debit += $amount;} else {$credit += $amount;} }
        if ($debit !== $credit) { throw new \DomainException('Ledger posting is not balanced.'); }
        return $this->repository->post($entries);
    }
}
