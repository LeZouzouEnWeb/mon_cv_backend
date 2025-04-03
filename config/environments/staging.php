<?php

/**
 * Configuration overrides for WP_ENV === 'staging'
 */

use Roots\WPConfig\Config;

/**
 * You should try to keep staging as close to production as possible. However,
 * should you need to, you can always override production configuration values
 * with `Config::define`.
 *
 * Example: `Config::define('WP_DEBUG', true);`
 * Example: `Config::define('DISALLOW_FILE_MODS', false);`
 */

 Config::define('WP_DEBUG', true);
 Config::define('WP_DEBUG_LOG', true);
 Config::define('WP_DEBUG_DISPLAY', false);
 @ini_set('display_errors', 0);
 
 // Emplacement du fichier de log
 Config::define('WP_DEBUG_LOG', 'storage/logs/debug.log');
 Config::define('SAVEQUERIES', true);

Config::define('DISALLOW_INDEXING', true);
