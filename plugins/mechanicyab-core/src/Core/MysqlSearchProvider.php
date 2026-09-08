<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\SearchProvider;
use MechanicYab\Core\Contracts\SearchRequest;

final class MysqlSearchProvider implements SearchProvider
{
    public function __construct(private readonly object $wpdb, private readonly OrganicRanking $ranking = new OrganicRanking()) {}

    public function search(SearchRequest $request): array
    {
        $table = $this->table();
        $where = ["m.deleted_at IS NULL", "m.status = 'active'", "m.publication_status = 'published'"];
        $params = [];
        if ($request->query !== '') {
            $where[] = '(m.name LIKE %s OR m.description LIKE %s)';
            $needle = '%' . $this->wpdb->esc_like($request->query) . '%';
            $params[] = $needle;
            $params[] = $needle;
        }
        if (isset($request->filters['location_id'])) {
            $where[] = 'm.location_id = %d';
            $params[] = (int) $request->filters['location_id'];
        }
        if (isset($request->filters['min_rating'])) {
            $where[] = 'm.average_rating >= %f';
            $params[] = (float) $request->filters['min_rating'];
        }
        if (isset($request->filters['service_id'])) {
            $where[] = "EXISTS (SELECT 1 FROM {$this->table('mechanic_services')} ms WHERE ms.mechanic_id = m.id AND ms.service_id = %d AND ms.status = 'active')";
            $params[] = (int) $request->filters['service_id'];
        }
        foreach (['brand_id', 'model_id', 'trim_id'] as $vehicleKey) {
            if (isset($request->filters[$vehicleKey])) {
                $where[] = "EXISTS (SELECT 1 FROM {$this->table('mechanic_service_vehicles')} msv INNER JOIN {$this->table('mechanic_services')} ms2 ON ms2.id = msv.mechanic_service_id WHERE ms2.mechanic_id = m.id AND msv.{$vehicleKey} = %d)";
                $params[] = (int) $request->filters[$vehicleKey];
            }
        }
        if (!empty($request->filters['verified'])) {
            $where[] = "m.verification_status = 'approved'";
        }
        if (isset($request->filters['min_price']) || isset($request->filters['max_price'])) {
            $priceWhere = "mp.mechanic_id = m.id AND mp.status = 'published'";
            if (isset($request->filters['min_price'])) {
                $priceWhere .= ' AND mp.price_max >= %f';
                $params[] = (float) $request->filters['min_price'];
            }
            if (isset($request->filters['max_price'])) {
                $priceWhere .= ' AND mp.price_min <= %f';
                $params[] = (float) $request->filters['max_price'];
            }
            $where[] = "EXISTS (SELECT 1 FROM {$this->table('mechanic_prices')} mp WHERE {$priceWhere})";
        }
        if (isset($request->filters['latitude'], $request->filters['longitude'], $request->filters['radius_km'])) {
            $latitude = (float) $request->filters['latitude'];
            $longitude = (float) $request->filters['longitude'];
            $radius = max(0.1, (float) $request->filters['radius_km']);
            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                throw new \InvalidArgumentException('Invalid search coordinates.');
            }
            $distance = '(6371 * ACOS(COS(RADIANS(%f)) * COS(RADIANS(m.latitude)) * COS(RADIANS(m.longitude) - RADIANS(%f)) + SIN(RADIANS(%f)) * SIN(RADIANS(m.latitude))))';
            $where[] = $distance . ' <= %f';
            array_push($params, $latitude, $longitude, $latitude, $radius);
        }
        if (!empty($request->filters['open_now'])) {
            $where[] = "EXISTS (SELECT 1 FROM {$this->table('mechanic_hours')} mh WHERE mh.mechanic_id = m.id AND mh.day_of_week = %d AND mh.is_open = 1 AND mh.open_time <= %s AND mh.close_time >= %s)";
            $params[] = (int) gmdate('w');
            $params[] = gmdate('H:i:s');
            $params[] = gmdate('H:i:s');
        }
        $limit = $request->perPage;
        $offset = $request->offset();
        $sql = "SELECT m.id, m.name, m.slug, m.description, m.phone, m.location_id, m.verification_status, m.average_rating, m.review_count, m.profile_completion_percent FROM {$table} m WHERE " . implode(' AND ', $where) . ' ORDER BY m.verification_status DESC, m.average_rating DESC, m.review_count DESC, m.id DESC LIMIT %d OFFSET %d';
        $params[] = $limit;
        $params[] = $offset;
        $prepared = $this->wpdb->prepare($sql, ...$params);
        $rows = $this->wpdb->get_results($prepared, ARRAY_A);
        $items = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $items[] = $this->ranking->score((array) $row, $request->query);
        }
        return [
            'items' => $items,
            'meta' => ['page' => $request->page, 'per_page' => $request->perPage, 'returned' => count($items), 'organic' => true],
        ];
    }

    private function table(): string
    {
        if (!isset($this->wpdb->prefix)) {
            throw new \RuntimeException('WordPress database prefix is unavailable.');
        }
        return $this->wpdb->prefix . 'my_mechanics';
    }
}
