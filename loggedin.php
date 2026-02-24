<?php
/**
 * Main plugin header.
 *
 * @package LoggedIn
 *
 * Plugin Name:         Loggedin - Limit Concurrent Sessions
 * Plugin URI:          https://duckdev.com/products/loggedin-limit-active-logins/
 * Description:         Limit an account to a specific number of simultaneous logins across all devices.
 * Version:             2.0.4
 * Author:              Joel James
 * Author URI:          https://duckdev.com/
 * Donate link:         https://paypal.me/JoelCJ
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         loggedin
 * Domain Path:         /languages
 * Requires PHP:        7.4
 * Requires at least:   5.0
 *
 * Loggedin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Loggedin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Loggedin. If not, see <http://www.gnu.org/licenses/>.
 */

namespace DuckDev\Loggedin;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

// Plugin directory path.
define( 'LOGGEDIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LOGGEDIN_FILE', __FILE__ );

// Make sure loggedin is not already defined.
if ( ! function_exists( __NAMESPACE__ . '\\init' ) ) {
	/**
	 * Main instance of plugin.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	function init() {
		// Load text domain.
		load_plugin_textdomain(
			'loggedin',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);

		// Load autoloader.
		require __DIR__ . '/vendor/autoload.php';

		// Load classes.
		new Core();
		new Admin();
		new Addons();

		/**
		 * Action hook to execute after our plugin init.
		 *
		 * Use this hook to init addons.
		 *
		 * @since 1.3.1
		 */
		do_action( 'loggedin_init' );
	}
}

// Init the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
