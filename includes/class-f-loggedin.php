<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die( 'Damn it.! Dude you are looking for what?' );
}

/**
 * The main functionality of the plugin.
 *
 * @category   Core
 * @package    LoggedIn
 * @subpackage Public
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @link       https://thefoxe.com/products/loggedin
 */
class F_LoggedIn {

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

        add_filter( 'wp_authenticate_user', array( $this, 'validate' ) );
    }
    

    /**
     * Validate if the maximum active logins limit reached.
     * 
     * @param object $user User Object/ WPError
     *
     * @since  1.0.0
     * @access public
     * 
     * @return object User object or error object.
     */
    public function validate( $user ) {
        
        // If login validation failed already, return that error
        if ( is_wp_error( $user ) ) {
            return $user;
        }
        
        // Check if limit exceed
        if ( $this->reached_limit( $user->ID ) ) {
            return new WP_Error( 'error', $this->error_message() );
        }
        
        return $user;
    }
    
    /**
     * Check if the current user is allowed for another login.
     * 
     * Count all the active logins for the current user annd
     * check if that exceeds the maximum login limit set.
     * 
     * @param int $user_id User ID
     * 
     * @since  1.0.0
     * @access public
     * 
     * @return boolean Limit reached or not
     */
    private function reached_limit( $user_id ) {
        
        if ( $this->bypass( $user_id ) ) {
            return false;
        }
        
        // Get maximum active logins allowed.
        $maximum = intval( get_option( 'loggedin_maximum', 1 ) );
        
        $manager = WP_Session_Tokens::get_instance( $user_id );
	
        $count = count( $manager->get_all() );
        
        return $count >= $maximum;
    }
    
    /**
     * Custom login limit bypassing.
     * 
     * Filter to bypass login limit based on a condition.
     * You can make use of this filter if you want to bypass
     * some users or roles from limit limit.
     * 
     * @param int $user_id User ID
     * 
     * @return type
     */
    private function bypass( $user_id ) {
        
        // User loggedin_bypass() filter with $user_id parameter
        return (bool) apply_filters( 'loggedin_bypass', false, $user_id );
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
        
        $message = __( 'Maximum no. of active logins found for this account. Please logout from another device to continue.', F_LOGGEDIN_DOMAIN );
        
        return apply_filters( 'loggedin_error_message', $message );
    }

}
