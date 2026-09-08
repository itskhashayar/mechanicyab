<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class Health
{
    /** @return array{status: string, checks: array<string, array{status: string, detail: string}>} */
    public function report(): array
    {
        $checks = [
            'application' => ['status' => 'healthy', 'detail' => 'Core Plugin loaded.'],
            'wordpress' => ['status' => function_exists('get_bloginfo') ? 'healthy' : 'unknown', 'detail' => 'WordPress runtime detection.'],
            'database' => ['status' => function_exists('wpdb') || isset($GLOBALS['wpdb']) ? 'healthy' : 'unknown', 'detail' => 'Database runtime detection.'],
            'queue' => ['status' => 'unknown', 'detail' => 'Queue execution is not part of Stage 1.'],
            'scheduler' => ['status' => 'unknown', 'detail' => 'Scheduler execution is not part of Stage 1.'],
            'cache' => ['status' => 'unknown', 'detail' => 'Persistent cache is not part of Stage 1.'],
        ];
        $status = 'healthy';
        foreach ($checks as $check) {
            if ($check['status'] === 'failed') {
                $status = 'failed';
                break;
            }
            if ($check['status'] === 'unknown' && $status === 'healthy') {
                $status = 'degraded';
            }
        }
        return ['status' => $status, 'checks' => $checks];
    }
}
