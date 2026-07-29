<?php
/**
 * Plugin entry file.
 *
 * Defines the path / file constants, loads the Composer autoloader,
 * loads the text domain, and hands off to {@see Core::instance()} to
 * boot every module in phased order.
 *
 * This file is intentionally minimal — the rule of thumb is "no
 * runtime logic in the bootstrap." Each subsystem (settings storage,
 * session guard, admin UI, REST API, Freemius) lives behind its own
 * class under `includes/` and registers its own hooks from there.
 *
 * @package           DuckDev\Loggedin
 * @author            Joel James
 * @copyright         2025 Joel James
 * @license           GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Loggedin - Session Manager, Limit Concurrent Logins & Force Logout
 * Plugin URI:        https://duckdev.com/products/loggedin-limit-active-logins/
 * Description:       Limit an account to a specific number of simultaneous logins across all devices.
 * Version:           3.1.0
 * Author:            Joel James
 * Author URI:        https://duckdev.com/
 * Donate link:       https://paypal.me/JoelCJ
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       loggedin
 * Domain Path:       /languages
 * Requires PHP:      7.4
 * Requires at least: 6.0
 */

namespace DuckDev\Loggedin;

defined( 'WPINC' ) || die;

/**
 * Absolute filesystem path to the plugin directory, with trailing slash.
 *
 * Used everywhere we need to include or read a file shipped inside
 * the plugin (the autoloader, the React bundle in `build/`, the
 * language files, etc.). Defined here once so individual classes
 * can read it via `Plugin::dir()` without ever recomputing it.
 */
define( 'LOGGEDIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Absolute filesystem path to *this file*.
 *
 * Freemius needs the main plugin file path during instance creation
 * (it derives the plugin basename from it). Captured here so we don't
 * have to thread it through every constructor.
 */
define( 'LOGGEDIN_FILE', __FILE__ );

if ( ! function_exists( __NAMESPACE__ . '\\init' ) ) {
	/**
	 * Boot the plugin once core has finished loading.
	 *
	 * Wrapped in a `function_exists` guard so a second copy of the
	 * plugin loaded from a different directory (a stray symlink, a
	 * dev clone) can't redefine the function and silently shadow the
	 * first init call. The function:
	 *
	 *   1. Loads the plugin text domain so any string surfaced during
	 *      boot (e.g. an admin notice from the upgrader) is already
	 *      translatable.
	 *   2. Pulls in the Composer autoloader.
	 *   3. Hands off to {@see Core::instance()}, which is responsible
	 *      for wiring every module in order.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	function init(): void {
		load_plugin_textdomain(
			'loggedin',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);

		require __DIR__ . '/vendor/autoload.php';

		Core::instance();
	}
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
