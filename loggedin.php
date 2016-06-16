<?php
/**
 * Plugin Name:     LoggedIn
 * Plugin URI:      https://wordpress.org/plugins/loggedin/
 * Description:     Light weight plugin to limit maximum number of active logins for a user account.
 * Version:         1.0.0
 * Author:          Joel James
 * Author URI:      https://thefoxe.com/
 * Donate link:     https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XUVWY8HUBUXY4
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
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die( 'Damn it.! Dude you are looking for what?' );
}

if ( ! class_exists( 'Foxe_LoggedIn' ) ) {
    
    // Constants array
    $constants = array(
        'FLOGGEDIN_NAME' => 'loggedin',
        'FLOGGEDIN_DOMAIN' => 'loggedin',
        'FLOGGEDIN_PATH' => plugins_url( '', __FILE__ ),
        'FLOGGEDIN_PLUGIN_DIR' => dirname( __FILE__ ),
        'FLOGGEDIN_SETTINGS_PAGE' => admin_url('options-general.php?page=floggedin-settings'),
        'FLOGGEDIN_VERSION' => '1.0.0',
        'FLOGGEDIN_PERMISSION' => 'manage_options'
    );

    foreach ( $constants as $constant => $value ) {
        // Set constants if not set already
        if ( ! defined( $constant ) ) {
            define( $constant, $value );
        }
    }
    
    function run_floggedin() {
        
        // Only execute if not admin side
        if ( ! is_admin() ) {
            require FLOGGEDIN_PLUGIN_DIR . '/includes/class-foxe-loggedin.php';
            $common = new Foxe_LoggedIn();
        }
        
        // Only execute if admin side
        if ( is_admin() ) {
            require FLOGGEDIN_PLUGIN_DIR . '/includes/class-foxe-loggedin-admin.php';
            $admin = new Foxe_LoggedIn_Admin();
        }
    }
    
    // Start the plugin
    run_floggedin();
}

//*** Thank you for your interest in LoggedIn - Developed and managed by Joel James ***// 