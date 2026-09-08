<?php

declare(strict_types=1);

namespace MechanicYab\Theme;

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => __('Primary Navigation', 'mechanicyab')]);
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style('mechanicyab-theme', get_stylesheet_uri(), [], '0.2.0');
});

function core_available(): bool
{
    return defined('MechanicYab\\Core\\VERSION');
}

function core_status_notice(): void
{
    if (!current_user_can('manage_options') || core_available()) {
        return;
    }
    echo '<div class="notice notice-warning"><p>' . esc_html__('MechanicYab Core Plugin is required for the full product experience.', 'mechanicyab') . '</p></div>';
}
add_action('admin_notices', __NAMESPACE__ . '\\core_status_notice');
