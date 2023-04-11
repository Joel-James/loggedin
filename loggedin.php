<?php
/**
 * Main plugin header.
 *
 * @package LoggedIn
 *
 * Plugin Name:     Loggedin - Limit Active Logins
 * Plugin URI:      https://duckdev.com/products/loggedin-limit-active-logins/
 * Description:     Light weight plugin to limit number of active logins from an account. Set maximum number of concurrent logins a user can have from multiple places.
 * Version:         1.4.0
 * Author:          Joel James
 * Author URI:      https://duckdev.com/
 * Donate link:     https://paypal.me/JoelCJ
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     loggedin
 * Domain Path:     /languages
 *
 * LoggedIn is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * LoggedIn is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LoggedIn. If not, see <http://www.gnu.org/licenses/>.
 */

namespace DuckDev\LoggedIn;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

// Make sure loggedin is not already defined.
if ( ! function_exists( __NAMESPACE__ . '\\init' ) ) {
	/**
	 * Main instance of plugin.
	 *
	 * @since 1.3.0
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

		// Load required files.
		require dirname( __FILE__ ) . '/includes/class-core.php';
		require dirname( __FILE__ ) . '/includes/class-admin.php';

		// Load classes.
		new Core();
		new Admin();

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
