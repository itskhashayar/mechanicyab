<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

final class PaymentRequest
{
    public function __construct(public readonly string $orderKey, public readonly int $amount, public readonly string $currency = 'IRR', public readonly string $callbackUrl = '') { if ($this->amount < 1000 || $this->orderKey === '' || !filter_var($this->callbackUrl, FILTER_VALIDATE_URL)) { throw new \InvalidArgumentException('Invalid payment request.'); } }
}

interface PaymentGateway
{
    /** @return array{accepted: bool, reference: string|null, redirect_url: string|null, status: string} */
    public function request(PaymentRequest $request): array;
    /** @return array{verified: bool, duplicate: bool, reference: string|null, amount: int, status: string} */
    public function verify(string $reference, int $amount, string $orderKey): array;
    /** @return array{status: string, reference: string|null} */
    public function inquiry(string $reference, string $orderKey): array;
}

interface PaymentRepository
{
    /** @param array<string, mixed> $data */
    public function create(array $data): int;
    /** @return array<string, mixed>|null */
    public function findByOrder(string $orderKey): ?array;
    public function markRequested(int $id, string $reference): bool;
    public function finalize(int $id, string $status, string $reference, int $amount): bool;
}
