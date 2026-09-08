<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class SchemaManager
{
    public const VERSION = 3;
    private const OPTION = 'mechanicyab_schema_version';
    private const LOCK = 'mechanicyab_schema_migration_lock';

    /** @var array<string, callable(string): string> */
    private array $definitions;

    public function __construct()
    {
        $this->definitions = [
            'users' => fn (string $table): string => "CREATE TABLE {$table} (\n                wp_user_id bigint(20) unsigned NOT NULL,\n                mobile varchar(20) NULL,\n                mobile_verified_at datetime NULL,\n                first_name varchar(100) NULL,\n                last_name varchar(100) NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                last_login_at datetime NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                deleted_at datetime NULL,\n                PRIMARY KEY (wp_user_id),\n                UNIQUE KEY mobile (mobile),\n                KEY status (status),\n                KEY last_login_at (last_login_at)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'roles' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                name varchar(100) NOT NULL,\n                slug varchar(100) NOT NULL,\n                description text NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY name (name),\n                UNIQUE KEY slug (slug)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'permissions' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                name varchar(150) NOT NULL,\n                slug varchar(150) NOT NULL,\n                group_name varchar(100) NOT NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY name (name),\n                UNIQUE KEY slug (slug),\n                KEY group_name (group_name)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'role_user' => fn (string $table): string => "CREATE TABLE {$table} (\n                wp_user_id bigint(20) unsigned NOT NULL,\n                role_id bigint(20) unsigned NOT NULL,\n                created_at datetime NOT NULL,\n                PRIMARY KEY (wp_user_id, role_id),\n                KEY role_id (role_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'permission_role' => fn (string $table): string => "CREATE TABLE {$table} (\n                permission_id bigint(20) unsigned NOT NULL,\n                role_id bigint(20) unsigned NOT NULL,\n                created_at datetime NOT NULL,\n                PRIMARY KEY (permission_id, role_id),\n                KEY role_id (role_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'user_preferences' => fn (string $table): string => "CREATE TABLE {$table} (\n                wp_user_id bigint(20) unsigned NOT NULL,\n                language varchar(10) NOT NULL DEFAULT 'fa',\n                timezone varchar(64) NOT NULL DEFAULT 'Asia/Tehran',\n                notification_settings longtext NULL,\n                privacy_settings longtext NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (wp_user_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'module_settings' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                module_id varchar(100) NOT NULL,\n                setting_key varchar(150) NOT NULL,\n                setting_value longtext NULL,\n                is_secret tinyint(1) NOT NULL DEFAULT 0,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY module_setting (module_id, setting_key),\n                KEY module_id (module_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'module_states' => fn (string $table): string => "CREATE TABLE {$table} (\n                module_id varchar(100) NOT NULL,\n                state varchar(30) NOT NULL DEFAULT 'active',\n                version varchar(30) NOT NULL,\n                reason text NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (module_id),\n                KEY state (state)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'locations' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                parent_id bigint(20) unsigned NULL,\n                type varchar(30) NOT NULL,\n                name varchar(150) NOT NULL,\n                slug varchar(180) NOT NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                sort_order int NOT NULL DEFAULT 0,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                deleted_at datetime NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY type_slug (type, slug),\n                KEY parent_id (parent_id),\n                KEY type_status (type, status)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'service_categories' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                parent_id bigint(20) unsigned NULL,\n                name varchar(150) NOT NULL,\n                slug varchar(180) NOT NULL,\n                description text NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                sort_order int NOT NULL DEFAULT 0,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                deleted_at datetime NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY slug (slug),\n                KEY parent_id (parent_id),\n                KEY status (status)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'services' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                category_id bigint(20) unsigned NOT NULL,\n                name varchar(200) NOT NULL,\n                slug varchar(220) NOT NULL,\n                description text NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                sort_order int NOT NULL DEFAULT 0,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                deleted_at datetime NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY slug (slug),\n                KEY category_status (category_id, status)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'vehicle_brands' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                name varchar(150) NOT NULL,\n                slug varchar(180) NOT NULL,\n                country varchar(100) NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY slug (slug),\n                KEY status (status)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'vehicle_models' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                brand_id bigint(20) unsigned NOT NULL,\n                name varchar(150) NOT NULL,\n                slug varchar(180) NOT NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY brand_slug (brand_id, slug),\n                KEY status (status)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'vehicle_trims' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                model_id bigint(20) unsigned NOT NULL,\n                name varchar(150) NOT NULL,\n                slug varchar(180) NOT NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY model_slug (model_id, slug),\n                KEY status (status)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanics' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                owner_user_id bigint(20) unsigned NOT NULL,\n                name varchar(200) NOT NULL,\n                slug varchar(220) NOT NULL,\n                description text NULL,\n                phone varchar(20) NOT NULL,\n                secondary_phone varchar(20) NULL,\n                whatsapp_phone varchar(20) NULL,\n                website_url varchar(255) NULL,\n                instagram_url varchar(255) NULL,\n                address text NOT NULL,\n                latitude decimal(10,7) NULL,\n                longitude decimal(10,7) NULL,\n                location_id bigint(20) unsigned NOT NULL,\n                status varchar(30) NOT NULL DEFAULT 'draft',\n                publication_status varchar(30) NOT NULL DEFAULT 'draft',\n                verification_status varchar(30) NOT NULL DEFAULT 'unverified',\n                profile_completion_percent tinyint unsigned NOT NULL DEFAULT 0,\n                average_rating decimal(3,2) NOT NULL DEFAULT 0,\n                review_count int unsigned NOT NULL DEFAULT 0,\n                response_rate decimal(5,2) NOT NULL DEFAULT 0,\n                published_at datetime NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                deleted_at datetime NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY slug (slug),\n                KEY owner_user_id (owner_user_id),\n                KEY location_id (location_id),\n                KEY publication_status (publication_status),\n                KEY verification_status (verification_status),\n                KEY rating_count (average_rating, review_count)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_locations' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_id bigint(20) unsigned NOT NULL,\n                location_id bigint(20) unsigned NOT NULL,\n                is_primary tinyint(1) NOT NULL DEFAULT 1,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY mechanic_location (mechanic_id, location_id),\n                KEY location_id (location_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_business_profiles' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_id bigint(20) unsigned NOT NULL,\n                business_type varchar(100) NULL,\n                established_year smallint NULL,\n                license_reference varchar(150) NULL,\n                business_description text NULL,\n                parking_available tinyint(1) NULL,\n                mobile_service tinyint(1) NOT NULL DEFAULT 0,\n                emergency_service tinyint(1) NOT NULL DEFAULT 0,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY mechanic_id (mechanic_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_verifications' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_id bigint(20) unsigned NOT NULL,\n                type varchar(50) NOT NULL,\n                status varchar(30) NOT NULL DEFAULT 'pending',\n                submitted_by bigint(20) unsigned NOT NULL,\n                reviewed_by bigint(20) unsigned NULL,\n                notes text NULL,\n                verified_at datetime NULL,\n                expires_at datetime NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                KEY mechanic_status (mechanic_id, status),\n                KEY expires_at (expires_at)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_hours' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_id bigint(20) unsigned NOT NULL,\n                day_of_week tinyint unsigned NOT NULL,\n                is_open tinyint(1) NOT NULL DEFAULT 1,\n                open_time time NULL,\n                close_time time NULL,\n                break_start time NULL,\n                break_end time NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY mechanic_day (mechanic_id, day_of_week)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_special_hours' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_id bigint(20) unsigned NOT NULL,\n                date date NOT NULL,\n                is_closed tinyint(1) NOT NULL DEFAULT 0,\n                open_time time NULL,\n                close_time time NULL,\n                reason varchar(255) NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY mechanic_date (mechanic_id, date)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_services' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_id bigint(20) unsigned NOT NULL,\n                service_id bigint(20) unsigned NOT NULL,\n                description text NULL,\n                duration_minutes int unsigned NULL,\n                status varchar(30) NOT NULL DEFAULT 'active',\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                UNIQUE KEY mechanic_service (mechanic_id, service_id),\n                KEY service_id (service_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_service_vehicles' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_service_id bigint(20) unsigned NOT NULL,\n                brand_id bigint(20) unsigned NULL,\n                model_id bigint(20) unsigned NULL,\n                trim_id bigint(20) unsigned NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                KEY mechanic_service_id (mechanic_service_id),\n                KEY vehicle_match (brand_id, model_id, trim_id)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            'mechanic_prices' => fn (string $table): string => "CREATE TABLE {$table} (\n                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                mechanic_id bigint(20) unsigned NOT NULL,\n                service_id bigint(20) unsigned NOT NULL,\n                brand_id bigint(20) unsigned NULL,\n                model_id bigint(20) unsigned NULL,\n                trim_id bigint(20) unsigned NULL,\n                price_amount decimal(15,2) NULL,\n                price_min decimal(15,2) NULL,\n                price_max decimal(15,2) NULL,\n                currency varchar(10) NOT NULL DEFAULT 'IRR',\n                description text NULL,\n                status varchar(30) NOT NULL DEFAULT 'draft',\n                published_at datetime NULL,\n                created_at datetime NOT NULL,\n                updated_at datetime NOT NULL,\n                PRIMARY KEY (id),\n                KEY mechanic_service (mechanic_id, service_id),\n                KEY vehicle_match (mechanic_id, brand_id, model_id),\n                KEY status (status)\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        ];
    }

    /** @return array<string, string> */
    public function tableNames(?string $prefix = null): array
    {
        $prefix ??= $this->prefix();
        $tables = [];
        foreach (array_keys($this->definitions) as $name) {
            $tables[$name] = $prefix . 'my_' . $name;
        }
        return $tables;
    }

    public function migrate(): void
    {
        global $wpdb;
        if (!isset($wpdb) || !is_object($wpdb)) {
            throw new \RuntimeException('WordPress database runtime is unavailable.');
        }
        if ($this->currentVersion() >= self::VERSION) {
            return;
        }
        if (!$this->acquireLock()) {
            throw new \RuntimeException('Schema migration is already in progress.');
        }
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        try {
            foreach ($this->definitions as $name => $definition) {
                dbDelta($definition($this->prefix() . 'my_' . $name));
            }
            if (function_exists('update_option')) {
                update_option(self::OPTION, self::VERSION, false);
            }
        } catch (\Throwable $exception) {
            $this->releaseLock();
            throw new \RuntimeException('Schema migration failed; version was not advanced.', 0, $exception);
        }
        $this->releaseLock();
    }

    /** @return array{destructive: bool, actions: list<string>} */
    public function rollbackPlan(): array
    {
        return [
            'destructive' => false,
            'actions' => ['Restore the previous code release and rerun verification.', 'Do not drop or reset Stage 2/3 tables.'],
        ];
    }

    private function acquireLock(): bool
    {
        if (!function_exists('add_option')) {
            return true;
        }
        return (bool) add_option(self::LOCK, time(), '', 'no');
    }

    private function releaseLock(): void
    {
        if (function_exists('delete_option')) {
            delete_option(self::LOCK);
        }
    }

    public function currentVersion(): int
    {
        return function_exists('get_option') ? (int) get_option(self::OPTION, 0) : 0;
    }

    /** @return list<string> */
    public function pendingMigrations(): array
    {
        return match (true) {
            $this->currentVersion() < 1 => ['stage-2-core-schema-v1', 'stage-3-reference-schema-v2', 'stage-4-mechanics-schema-v3'],
            $this->currentVersion() < 2 => ['stage-3-reference-schema-v2', 'stage-4-mechanics-schema-v3'],
            $this->currentVersion() < 3 => ['stage-4-mechanics-schema-v3'],
            default => [],
        };
    }

    /** @return array<string, string> */
    public function identityContract(): array
    {
        return [
            'canonical_table' => 'wp_users',
            'canonical_key' => 'ID',
            'profile_table' => 'my_users',
            'profile_key' => 'wp_user_id',
            'role_link_key' => 'wp_user_id',
            'preference_key' => 'wp_user_id',
        ];
    }

    /** @return array{status: string, version: int, tables: array<string, string>} */
    public function healthReport(): array
    {
        return [
            'status' => $this->currentVersion() >= self::VERSION ? 'healthy' : 'pending',
            'version' => $this->currentVersion(),
            'tables' => $this->tableNames(),
        ];
    }

    private function prefix(): string
    {
        global $wpdb;
        if (isset($wpdb) && isset($wpdb->prefix)) {
            return (string) $wpdb->prefix;
        }
        return 'wp_';
    }
}
