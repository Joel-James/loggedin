<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die( 'Damn it.! Dude you are looking for what?' );
}

/**
 * Admin side functionality of the plugin.
 *
 * @category   Core
 * @package    LoggedIn
 * @subpackage Admin
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @link       https://thefoxe.com/products/loggedin
 */
class F_LoggedIn_Admin {

    /**
     * Initialize the class and set its properties.
     * 
     * We register all our admin hooks here.
     *
     * @since  1.0.0
     * @access public
     * 
     * @return void
     */
    public function __construct() {

        add_action('admin_init', array( $this, 'options_page' ) );
    }

    /**
     * Create new option field label to the default settings page.
     *
     * @since  1.0.0
     * @access public
     * @uses   register_setting()   To register new setting.
     * @uses   add_settings_field() To add new field to for the setting.
     * 
     * @return void
     */
    public function options_page() {

        register_setting( 'general', 'loggedin_maximum' );

        add_settings_field(
            'loggedin_label',
            '<label for="dpr">' . __( 'Maximum Active Logins', F_LOGGEDIN_DOMAIN ) . '</label>',
            array( &$this, 'fields' ),
            'general'
        );
    }

    /**
     * Create new options field to the settings page.
     *
     * @since  1.0.0
     * @access public
     * @uses   get_option() To get the option value.
     * 
     * @return void
     */
    public function fields() {
        
        // get settings value
        $value = get_option( 'loggedin_maximum', 3 );
        echo '<input type="number" name="loggedin_maximum" min="1" value="' . intval( $value ) . '" />';
        echo '<p class="description">' . __( 'Set the maximum no. of active logins a user account can have.', F_LOGGEDIN_DOMAIN ) . '</p>';
        echo '<p class="description">' . __( 'If this limit reached, next login request will be failed and user will have to logout from one device to continue.', F_LOGGEDIN_DOMAIN ) . '</p>';
        echo '<p class="description"><strong>' . __( 'Note: ', F_LOGGEDIN_DOMAIN ) . '</strong>' . __( 'Even if the browser is closed, login session may exist.', F_LOGGEDIN_DOMAIN ) . '</p>';
    }

}
