<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class HoursService
{
    /** @param array<string, mixed> $hours @return array<string, mixed> */
    public function normalize(array $hours): array
    {
        $day = (int) ($hours['day_of_week'] ?? -1);
        if ($day < 0 || $day > 6) {
            throw new \InvalidArgumentException('day_of_week must be between 0 and 6.');
        }
        $isOpen = (bool) ($hours['is_open'] ?? true);
        $hours['day_of_week'] = $day;
        $hours['is_open'] = $isOpen ? 1 : 0;
        if (!$isOpen) {
            $hours['open_time'] = null;
            $hours['close_time'] = null;
            return $hours;
        }
        $open = (string) ($hours['open_time'] ?? '');
        $close = (string) ($hours['close_time'] ?? '');
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $open) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $close)) {
            throw new \InvalidArgumentException('Open and close times must use HH:MM or HH:MM:SS.');
        }
        if ($open === $close) {
            throw new \InvalidArgumentException('Open and close times cannot be equal.');
        }
        $hours['open_time'] = substr($open, 0, 5) . ':00';
        $hours['close_time'] = substr($close, 0, 5) . ':00';
        return $hours;
    }
}
