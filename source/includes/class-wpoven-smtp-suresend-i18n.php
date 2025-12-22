<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.wpoven.com/plugins/
 * @since      1.0.0
 *
 * @package    Wpoven_Smtp_Suresend
 * @subpackage Wpoven_Smtp_Suresend/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wpoven_Smtp_Suresend
 * @subpackage Wpoven_Smtp_Suresend/includes
 * @author     WPOven <contact@wpoven.com>
 */
class Wpoven_Smtp_Suresend_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'wpoven-smtp-suresend',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
