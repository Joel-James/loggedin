<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die( 'Damn it.! Dude you are looking for what?' );
}

/**
 * The common functionality of the plugin.
 *
 * This class contains the entire plugin functionality.
 *
 * @category   Core
 * @package    LoggedIn
 * @subpackage Public
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @link       https://thefoxe.com/products/loggedin
 */
class Foxe_LoggedIn {

    /**
     * Initialize the class and set its properties.
     * 
     * We register all our common hooks here.
     *
     * @since  1.0.0
     * @access public
     * 
     * @return void
     */
    public function __construct() {

        add_action( 'wp_login', array( $this, 'increase' ) );
        add_filter( 'wp_authenticate_user', array( $this, 'validate' ) );
        add_action( 'wp_logout', array( $this, 'decrease' ) );
    }
    
    /**
     * Validate if the maximum active logins limit reached.
     *
     * @since  1.0.0
     * @access public
     * @uses   get_transient() To get temporary option value.
     * 
     * @return void
     */
    public function validate( $user ) {
        
        // If login validation failed already, return that error
        if ( is_wp_error( $user ) ) {
            return $user;
        }
        
        // Get the online users list
        $online_users = get_transient('online_status');
        // Set count as 0
        $count = 0;
        // Get username of the user
        $username = $user->user_login;
        
        // Get the active logins count for the user
        if ( isset( $online_users[ $username ] ) ) {
            $count = intval( $online_users[ $username ] );
        }

        // Get maximum active logins allowed.
        // If not set, allow unlimitted
        $maximum = intval( get_option( 'loggedin_maximum', $count + 1 ) );
        
        // If incase count gone negative, make it positive
        $count = ( $count > 0 ) ? intval( $count ) : 0;
        // Check if limit exceed
        if ( $count >= $maximum ) {
            return new WP_Error( 'error', $this->error_message() );
        }
        
        return $user;
    }

    /**
     * Set the user login status when he login.
     *
     * @since  1.0.0
     * @access public
     * @uses   get_transient() To get temporary option value.
     * @uses   set_transient() To set temporary option value.
     * 
     * @return void
     */
    public function increase( $username ) {
        
        // Get the online users list
        $online_users = get_transient('online_status');

        // Set count as 0
        $count = 0;
        
        // Get the active logins count for the user
        if ( isset( $online_users[ $username ] ) ) {
            $count = intval( $online_users[ $username ] );
        }
        
        // Increase the count by 1
        $online_users[ $username ] = $count + 1;

        // Update/Set the logged in count for the user
        set_transient('online_status', $online_users ); // 30 mins 
    }
    
    /**
     * Set the user login status count when he logout.
     *
     * @since  1.0.0
     * @access public
     * @uses   get_transient() To get temporary option value.
     * @uses   set_transient() To set temporary option value.
     * 
     * @return void
     */
    public function decrease() {
            
        // Get the online users list
        $online_users = get_transient('online_status');

        // Get current username
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;
            
        // Set count as 1
        $count = 1;
        
        // Get the active logins count for the user
        if ( isset( $online_users[ $username ] ) ) {
            $count = intval( $online_users[ $username ] );
        }
        
        $count = $count < 1 ? 1 : $count;
        
        // If logged in count found, decrease by 1
        if ( isset( $online_users[ $username ] ) ) {
            // Decrease logged in count by 1
            $online_users[ $username ] = $count - 1;
            set_transient( 'online_status', $online_users );
        }
    }
    
    /**
     * Error message text if user active logins count is maximum
     * 
     * @since  1.0.0
     * @access public
     * 
     * @return string Error message
     */
    private function error_message() {
        
        $message = 'Maximum no. of active logins found for this account. Please logout from another device to continue.';
        
        return apply_filters( 'loggedin_error_message', $message );
    }

}