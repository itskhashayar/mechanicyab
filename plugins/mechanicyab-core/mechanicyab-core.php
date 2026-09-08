<?php
/**
 * Plugin Name: MechanicYab Core
 * Description: Core application foundation for MechanicYab.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Requires at least: 6.4
 * Text Domain: mechanicyab
 */

declare(strict_types=1);

namespace MechanicYab\Core;

use MechanicYab\Core\Contracts\Response;
use MechanicYab\Core\Core\Health;
use MechanicYab\Core\Core\ModuleRegistry;
use MechanicYab\Core\Core\SchemaManager;
use MechanicYab\Core\Modules\CoreModule;

if (!defined('ABSPATH')) {
    exit;
}

const VERSION = '0.1.0';
const API_NAMESPACE = 'mechanicyab/v1';

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = __NAMESPACE__ . '\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = __DIR__ . '/src/' . $relative . '.php';
        if (is_readable($file)) {
            require_once $file;
        }
    });
}

final class Plugin
{
    private ModuleRegistry $registry;
    private Health $health;
    private SchemaManager $schema;

    public function __construct()
    {
        $this->registry = new ModuleRegistry();
        $this->health = new Health();
        $this->schema = new SchemaManager();
    }

    public static function activate(): void
    {
        (new SchemaManager())->migrate();
    }

    public function boot(): void
    {
        $this->registry->register(new CoreModule());
        $this->registry->boot();
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('mechanicyab', [$this, 'cli']);
        }
    }

    public function registerRestRoutes(): void
    {
        register_rest_route(API_NAMESPACE, '/diagnostics', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('mechanicyab_view_diagnostics'),
            'callback' => fn (): array => (new Response(true, [
                'version' => VERSION,
                'modules' => $this->registry->all(),
                'health' => $this->health->report(),
                'schema' => $this->schema->healthReport(),
            ]))->toArray(),
        ]);
    }

    public function registerAdminMenu(): void
    {
        add_menu_page(
            'مکانیک‌یاب',
            'مکانیک‌یاب',
            'mechanicyab_view_diagnostics',
            'mechanicyab',
            [$this, 'renderAdminPage'],
            'dashicons-admin-tools',
        );
    }

    public function registerSettings(): void
    {
        register_setting('mechanicyab', 'mechanicyab_settings', [
            'type' => 'array',
            'default' => [],
            'sanitize_callback' => static fn (mixed $value): array => is_array($value) ? $value : [],
        ]);
    }

    public function renderAdminPage(): void
    {
        if (!current_user_can('mechanicyab_view_diagnostics')) {
            wp_die(esc_html__('Permission denied.', 'mechanicyab'));
        }
        $health = $this->health->report();
        echo '<div class="wrap"><h1>مکانیک‌یاب</h1><p>Core Foundation v' . esc_html(VERSION) . '</p><pre>' . esc_html((string) wp_json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></div>';
    }

    public function cli(array $args, array $assocArgs): void
    {
        $command = $args[0] ?? 'status';
        $payload = match ($command) {
            'status' => ['version' => VERSION, 'status' => 'active'],
            'modules:list' => $this->registry->all(),
            'modules:health', 'health' => [
                'application' => $this->health->report(),
                'schema' => $this->schema->healthReport(),
            ],
            'migrate' => $this->migrateFromCli(),
            default => ['error' => 'Unknown command. Use status, modules:list, or health.'],
        };
        \WP_CLI::line((string) wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function migrateFromCli(): array
    {
        $this->schema->migrate();
        return $this->schema->healthReport();
    }
}

register_activation_hook(__FILE__, [Plugin::class, 'activate']);

add_action('plugins_loaded', static function (): void {
    (new Plugin())->boot();
});
