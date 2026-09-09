<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\AnalyticsRepository;

final class AnalyticsService
{
    /** @var array<string, true> */
    private const CTA_IDS = [
        'CTA_FIND_MECHANIC'=>true,'CTA_CONTACT_MECHANIC'=>true,'CTA_GET_DIRECTIONS'=>true,'CTA_SAVE_MECHANIC'=>true,'CTA_VIEW_SERVICES'=>true,'CTA_VIEW_PRICES'=>true,'CTA_SUBMIT_REVIEW'=>true,'CTA_START_AI'=>true,'CTA_SUBSCRIBE'=>true,'CTA_VIEW_AD'=>true,'CTA_BID'=>true,'CTA_SHARE_MECHANIC'=>true,
    ];
    public function __construct(private readonly AnalyticsRepository $repository) {}
    /** @param array<string, mixed> $context */
    public function track(string $eventName, ?string $ctaId, array $context = []): bool
    {
        if (!preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/', $eventName)) { throw new \InvalidArgumentException('Invalid analytics event name.'); }
        if ($ctaId !== null && !isset(self::CTA_IDS[$ctaId])) { throw new \InvalidArgumentException('Unknown CTA id.'); }
        $allowed = ['entity_type','entity_id','page_type','city_id','neighborhood_id','service_id','vehicle_model_id','device_type','outcome'];
        $payload = ['event_name'=>$eventName,'cta_id'=>$ctaId,'occurred_at'=>gmdate('Y-m-d H:i:s')];
        foreach ($allowed as $key) { if (array_key_exists($key, $context)) { $payload[$key] = $context[$key]; } }
        if (!empty($context['session_id'])) { $payload['session_hash'] = hash_hmac('sha256', (string) $context['session_id'], function_exists('wp_salt') ? wp_salt('auth') : 'analytics'); }
        if (!empty($context['wp_user_id'])) { $payload['wp_user_id'] = (int) $context['wp_user_id']; }
        $payload['dedupe_key'] = hash('sha256', implode('|', [(string)$eventName,(string)$ctaId,(string)($payload['entity_id']??''),(string)($payload['session_hash']??''),(string)$payload['occurred_at']]));
        return $this->repository->record($payload);
    }
    /** @return list<string> */
    public function ctaIds(): array { return array_keys(self::CTA_IDS); }
}
