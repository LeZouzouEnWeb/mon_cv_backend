<?php
/**
 * Charge manuellement les plugins dans le dossier `mes-plugins`
 */

$custom_plugins_dir = __DIR__ . '/../plugins/mes-plugins';

foreach (glob($custom_plugins_dir . '/*', GLOB_ONLYDIR) as $plugin_folder) {
    $main_plugin_file = $plugin_folder . '/' . basename($plugin_folder) . '.php';
    if (file_exists($main_plugin_file)) {
        include_once $main_plugin_file;
    }
}
