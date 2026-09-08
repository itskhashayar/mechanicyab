<?php

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Stage 1 owns no Domain tables or Domain data. Only its small Foundation option is removed.
delete_option('mechanicyab_settings');
