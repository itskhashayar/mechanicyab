<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\SmsProvider;

final class KavenegarSmsProvider implements SmsProvider
{
    public function __construct(private readonly string $apiKey, private readonly string $template, private readonly int $timeout = 8) {}

    public function sendOtp(string $mobile, string $code): array
    {
        if ($this->apiKey === '' || $this->template === '') {
            throw new \RuntimeException('SMS provider is not configured.');
        }
        $url = 'https://api.kavenegar.com/v1/' . rawurlencode($this->apiKey) . '/verify/lookup.json';
        $response = wp_remote_post($url, ['timeout' => $this->timeout, 'body' => ['receptor' => $mobile, 'token' => $code, 'template' => $this->template]]);
        if (is_wp_error($response)) {
            throw new \RuntimeException('SMS transport failed.');
        }
        $status = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($status !== 200 || !is_array($body) || (int) ($body['return']['status'] ?? 0) !== 200) {
            throw new \RuntimeException('SMS provider rejected the request.');
        }
        return ['accepted' => true, 'reference' => isset($body['entries'][0]['messageid']) ? (string) $body['entries'][0]['messageid'] : null, 'code' => null];
    }
}
