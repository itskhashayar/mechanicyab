<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class Settings
{
    private const OPTION = 'mechanicyab_settings';

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = function_exists('get_option') ? get_option(self::OPTION, []) : [];
        return is_array($settings) && array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $settings = function_exists('get_option') ? get_option(self::OPTION, []) : [];
        $settings = is_array($settings) ? $settings : [];
        $settings[$key] = $value;
        if (function_exists('update_option')) {
            update_option(self::OPTION, $settings, false);
        }
    }
}
