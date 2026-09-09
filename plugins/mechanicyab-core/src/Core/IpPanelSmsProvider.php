<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\SmsProvider;

final class IpPanelSmsProvider implements SmsProvider
{
    public function __construct(private readonly string $token, private readonly string $patternCode, private readonly string $fromNumber, private readonly int $timeout = 8) {}
    public function sendOtp(string $mobile, string $code): array
    {
        if ($this->token === '' || $this->patternCode === '' || $this->fromNumber === '') { throw new \RuntimeException('SMS provider is not configured.'); }
        $response = wp_remote_post('https://edge.ippanel.com/v1/api/send', ['timeout' => $this->timeout, 'headers' => ['Authorization' => $this->token, 'Content-Type' => 'application/json'], 'body' => wp_json_encode(['sending_type' => 'pattern', 'from_number' => $this->fromNumber, 'code' => $this->patternCode, 'recipients' => [$mobile], 'params' => ['code' => $code]])]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300) { throw new \RuntimeException('SMS provider rejected the request.'); }
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        return ['accepted' => (bool) ($body['meta']['status'] ?? false), 'reference' => isset($body['data']['message_outbox_ids'][0]) ? (string) $body['data']['message_outbox_ids'][0] : null, 'code' => $body['meta']['message_code'] ?? null];
    }
}
