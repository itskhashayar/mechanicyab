<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\AnalyticsRepository;

final class WpdbAnalyticsRepository implements AnalyticsRepository
{
    public function __construct(private readonly object $wpdb) {}
    public function record(array $event): bool { $payload = $event + ['created_at'=>gmdate('Y-m-d H:i:s')]; $formats = []; foreach ($payload as $value) { $formats[] = is_int($value) ? '%d' : '%s'; } $result = $this->wpdb->insert($this->table('analytics_events'), $payload, $formats); return $result !== false || str_contains((string) ($this->wpdb->last_error ?? ''), 'Duplicate'); }
    public function daily(string $metricKey, string $from, string $to): array { $rows = $this->wpdb->get_results($this->wpdb->prepare("SELECT metric_key, bucket_date, dimensions, metric_value FROM {$this->table('analytics_daily_metrics')} WHERE metric_key = %s AND bucket_date BETWEEN %s AND %s ORDER BY bucket_date ASC", $metricKey, $from, $to), ARRAY_A); return is_array($rows) ? $rows : []; }
    private function table(string $name): string { if (!isset($this->wpdb->prefix)) { throw new \RuntimeException('WordPress database prefix is unavailable.'); } return $this->wpdb->prefix . 'my_' . $name; }
}
