<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.wpoven.com/plugins/wpoven-smtp-suresend
 * @since             1.0.0
 * @package           Wpoven_Smtp_Suresend
 *
 * @wordpress-plugin
 * Plugin Name:       WPOven SMTP Suresend
 * Plugin URI:        https://www.wpoven.com/plugins/wpoven-smtp-suresend
 * Description:       Activate the SMTP plugin to secure your site's email delivery by configuring the SMTP server of your preferred mail service.
 * Version:           1.0.2
 * Author:            WPOven
 * Author URI:        https://www.wpoven.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpoven-smtp-suresend
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WPOVEN_SMTP_SURESEND_VERSION', '1.0.2');
if (!defined('WPOVEN_SMTP_SURESEND_SLUG'))
	define('WPOVEN_SMTP_SURESEND_SLUG', 'wpoven-smtp-suresend');

define('WPOVEN_SMTP_SURESEND', 'WPOven SMTP Suresend');
define('WPOVEN_SMTP_SURESEND_ROOT_PL', __FILE__);
define('WPOVEN_SMTP_SURESEND_ROOT_URL', plugins_url('', WPOVEN_SMTP_SURESEND_ROOT_PL));
define('WPOVEN_SMTP_SURESEND_ROOT_DIR', dirname(WPOVEN_SMTP_SURESEND_ROOT_PL));
define('WPOVEN_SMTP_SURESEND_PLUGIN_DIR', plugin_dir_path(__DIR__));
define('WPOVEN_SMTP_SURESEND_PLUGIN_BASE', plugin_basename(WPOVEN_SMTP_SURESEND_ROOT_PL));
define('WPOVEN_SURESEND_PATH', realpath(plugin_dir_path(WPOVEN_SMTP_SURESEND_ROOT_PL)) . '/');


require_once plugin_dir_path(__FILE__) . 'includes/libraries/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$wpoven_smtp_suresend_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/baseapp/wpoven_suresend/',
	__FILE__,
	'wpoven-smtp-suresend'
);
$wpoven_smtp_suresend_update_checker->getVcsApi()->enableReleaseAssets();


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpoven-smtp-suresend-activator.php
 */
function wpoven_smtp_suresend_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-wpoven-smtp-suresend-activator.php';
	Wpoven_Smtp_Suresend_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpoven-smtp-suresend-deactivator.php
 */
function wpoven_smtp_suresend_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-wpoven-smtp-suresend-deactivator.php';
	Wpoven_Smtp_Suresend_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'wpoven_smtp_suresend_activate');
register_deactivation_hook(__FILE__, 'wpoven_smtp_suresend_deactivate');

register_activation_hook(__FILE__, function () {
	if (!wp_next_scheduled('wpoven_smtp_log_cleanup_event')) {
		wp_schedule_event(time(), 'hourly', 'wpoven_smtp_log_cleanup_event');
	}
});

register_deactivation_hook(__FILE__, function () {
	wp_clear_scheduled_hook('wpoven_smtp_log_cleanup_event');
});


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wpoven-smtp-suresend.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action('plugins_loaded', function () {
	$plugin = new Wpoven_Smtp_Suresend();
	$plugin->run();
});


function wpoven_smtp_suresend_plugin_settings_link($links)
{
	$settings_link = '<a href="' . admin_url('admin.php?page=' . WPOVEN_SMTP_SURESEND_SLUG) . '">Settings</a>';

	array_push($links, $settings_link);
	return $links;
}
add_filter('plugin_action_links_' . WPOVEN_SMTP_SURESEND_PLUGIN_BASE, 'wpoven_smtp_suresend_plugin_settings_link');
