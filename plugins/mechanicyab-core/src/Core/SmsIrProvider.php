<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\SmsProvider;

final class SmsIrProvider implements SmsProvider
{
    public function __construct(private readonly string $apiKey, private readonly int $templateId, private readonly int $timeout = 8) {}
    public function sendOtp(string $mobile, string $code): array
    {
        if ($this->apiKey === '' || $this->templateId < 1) { throw new \RuntimeException('SMS provider is not configured.'); }
        $response = wp_remote_post('https://api.sms.ir/v1/send/verify', ['timeout' => $this->timeout, 'headers' => ['X-API-KEY' => $this->apiKey, 'Content-Type' => 'application/json'], 'body' => wp_json_encode(['Mobile' => $mobile, 'TemplateId' => $this->templateId, 'Parameters' => [['Name' => 'CODE', 'Value' => $code]]])]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300) { throw new \RuntimeException('SMS provider rejected the request.'); }
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        return ['accepted' => (int) ($body['status'] ?? 0) === 1, 'reference' => isset($body['data']['messageId']) ? (string) $body['data']['messageId'] : null, 'code' => null];
    }
}
