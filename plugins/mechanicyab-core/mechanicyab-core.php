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
use MechanicYab\Core\Core\MechanicPublicResource;
use MechanicYab\Core\Core\MechanicService;
use MechanicYab\Core\Core\ModuleRegistry;
use MechanicYab\Core\Core\MysqlSearchProvider;
use MechanicYab\Core\Core\OpenDirectionsAdapter;
use MechanicYab\Core\Core\PublicRouteResolver;
use MechanicYab\Core\Core\SearchService;
use MechanicYab\Core\Core\SchemaManager;
use MechanicYab\Core\Modules\CoreModule;
use MechanicYab\Core\Modules\SearchModule;

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
    private PublicRouteResolver $routes;

    public function __construct()
    {
        $this->registry = new ModuleRegistry();
        $this->health = new Health();
        $this->schema = new SchemaManager();
        $this->routes = new PublicRouteResolver();
    }

    public static function activate(): void
    {
        (new SchemaManager())->migrate();
    }

    public function boot(): void
    {
        $this->registry->register(new CoreModule());
        $this->registry->register(new SearchModule());
        $this->registry->boot();
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('init', [$this->routes, 'register']);
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
        register_rest_route(API_NAMESPACE, '/mechanics/(?P<slug>[a-z0-9-]+)', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => function (\WP_REST_Request $request): \WP_REST_Response {
                $service = new MechanicService(new \MechanicYab\Core\Core\WpdbMechanicRepository($GLOBALS['wpdb']));
                try {
                    $data = (new MechanicPublicResource())->toResponse($service->publicBySlug((string) $request['slug']));
                    return new \WP_REST_Response((new Response(true, $data))->toArray(), 200);
                } catch (\Throwable) {
                    return new \WP_REST_Response((new Response(false, null, [], [['code' => 'mechanic_not_found']]))->toArray(), 404);
                }
            },
        ]);
        register_rest_route(API_NAMESPACE, '/mechanics', [
            'methods' => 'POST',
            'permission_callback' => static fn (): bool => current_user_can('mechanicyab_manage_mechanics'),
            'callback' => function (\WP_REST_Request $request): \WP_REST_Response {
                $service = new MechanicService(new \MechanicYab\Core\Core\WpdbMechanicRepository($GLOBALS['wpdb']));
                try {
                    $id = $service->create((array) $request->get_json_params(), (int) get_current_user_id());
                    return new \WP_REST_Response((new Response(true, ['id' => $id]))->toArray(), 201);
                } catch (\Throwable) {
                    return new \WP_REST_Response((new Response(false, null, [], [['code' => 'mechanic_create_failed']]))->toArray(), 422);
                }
            },
        ]);
        register_rest_route(API_NAMESPACE, '/search', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => function (\WP_REST_Request $request): \WP_REST_Response {
                $filters = [];
                foreach (['location_id', 'service_id', 'brand_id', 'model_id', 'trim_id', 'min_rating', 'min_price', 'max_price', 'latitude', 'longitude', 'radius_km', 'verified', 'open_now'] as $key) {
                    if ($request->get_param($key) !== null) {
                        $filters[$key] = $request->get_param($key);
                    }
                }
                $result = (new SearchService(new MysqlSearchProvider($GLOBALS['wpdb'])))->search(
                    (string) $request->get_param('q'),
                    $filters,
                    max(1, (int) $request->get_param('page')),
                    min(100, max(1, (int) ($request->get_param('per_page') ?: 20))),
                );
                return new \WP_REST_Response((new Response(true, $result))->toArray(), 200);
            },
        ]);
        register_rest_route(API_NAMESPACE, '/map/directions', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => function (\WP_REST_Request $request): \WP_REST_Response {
                try {
                    $result = (new OpenDirectionsAdapter())->directions(
                        (float) $request->get_param('from_lat'),
                        (float) $request->get_param('from_lng'),
                        (float) $request->get_param('to_lat'),
                        (float) $request->get_param('to_lng'),
                    );
                    return new \WP_REST_Response((new Response(true, $result))->toArray(), 200);
                } catch (\Throwable) {
                    return new \WP_REST_Response((new Response(false, null, [], [['code' => 'invalid_coordinates']]))->toArray(), 422);
                }
            },
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
