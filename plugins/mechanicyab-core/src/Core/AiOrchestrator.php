<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\AiCreditRepository;
use MechanicYab\Core\Contracts\AiProvider;
use MechanicYab\Core\Contracts\AiRequest;
use MechanicYab\Core\Contracts\AiResponse;

final class AiOrchestrator
{
    public function __construct(private readonly AiSafetyGuard $safety, private readonly AiCreditRepository $credits, private readonly ?AiProvider $provider = null, private readonly float $creditCost = 1.0) {}
    public function respond(int $userId, string $prompt, array $context = []): AiResponse
    {
        if ($userId < 1) { throw new \DomainException('Authenticated user is required.'); }
        $safety = $this->safety->inspect($prompt); if (!$safety['allowed']) { throw new \DomainException((string)$safety['message']); }
        if ($this->provider === null) { throw new \RuntimeException('AI provider is not configured; graceful degradation is active.'); }
        if ($this->credits->balance($userId) < $this->creditCost) { throw new \DomainException('AI credit limit reached.'); }
        $reference = hash('sha256', $userId.'|'.microtime(true).'|'.$prompt); if (!$this->credits->consume($userId, $this->creditCost, $reference)) { throw new \RuntimeException('AI credit reservation failed.'); }
        try { $response = $this->provider->complete(new AiRequest($prompt, $context)); $content=$this->safety->validateOutput($response->content); return new AiResponse($content,$response->inputTokens,$response->outputTokens,$response->latencyMs,$response->status); } catch (\Throwable $exception) { $this->credits->refund($userId,$this->creditCost,$reference); throw $exception; }
    }
}
