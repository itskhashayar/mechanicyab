<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

final class AiRequest { public function __construct(public readonly string $prompt, public readonly array $context = [], public readonly string $model = 'default') {} }
final class AiResponse { public function __construct(public readonly string $content, public readonly int $inputTokens = 0, public readonly int $outputTokens = 0, public readonly int $latencyMs = 0, public readonly string $status = 'completed') {} }
interface AiProvider { public function complete(AiRequest $request): AiResponse; }
interface AiCreditRepository { public function balance(int $userId): float; public function consume(int $userId, float $amount, string $reference): bool; public function refund(int $userId, float $amount, string $reference): bool; }
