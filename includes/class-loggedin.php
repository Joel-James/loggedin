<?php
/**
 * The main functionality of the plugin.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @category   Core
 * @package    Loggedin
 * @subpackage Public
 * @author     Joel James <me@joelsays.com>
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die( 'Well, get lost.' );

/**
 * Class Loggedin
 *
 * @since 1.0.0
 */
class Loggedin {

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
		// Use authentication filter.
		add_filter( 'wp_authenticate_user', array( $this, 'validate_block_logic' ) );
		// Use password check filter.
		add_filter( 'check_password', array( $this, 'validate_allow_logic' ), 10, 4 );
	}

	/**
	 * Validate if the maximum active logins limit reached.
	 *
	 * This check happens only after authentication happens and
	 * the login logic is "Allow".
	 *
	 * @param boolean $check    User Object/WPError.
	 * @param string  $password Plaintext user's password.
	 * @param string  $hash     Hash of the user's password to check against.
	 * @param int     $user_id  User ID.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public function validate_allow_logic( $check, $password, $hash, $user_id ) {
		// If the validation failed already, bail.
		if ( ! $check ) {
			return false;
		}

		// Do not allow new logins.
		if ( 'allow' === get_option( 'loggedin_logic', 'allow' ) ) {
			// Check if limit exceed.
			if ( $this->reached_limit( $user_id ) ) {
				if ( get_option( 'logout_oldest_session', false ) ) {
					$this->logout_oldest_session( $user_id );
				} else {
					$this->logout_all_sessions( $user_id );
				}
			}
		}

		return true;
	}


	/**
	 * Validate if the maximum active logins limit reached.
	 *
	 * This check happens only after authentication happens and
	 * the login logic is "Block".
	 *
	 * @param object $user User Object/WPError.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return object User object or error object.
	 */
	public function validate_block_logic( $user ) {
		// If login validation failed already, return that error.
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		// Only when block method.
		if ( 'block' === get_option( 'loggedin_logic', 'allow' ) ) {
			// Check if limit exceed.
			if ( $this->reached_limit( $user->ID ) ) {
				return new WP_Error( 'loggedin_reached_limit', $this->error_message() );
			}
		}

		return $user;
	}

	/**
	 * Log out all sessions for the user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 1.3.2
	 * @access private
	 *
	 * @return void
	 */
	private function logout_all_sessions( $user_id ) {
		$manager = WP_Session_Tokens::get_instance( $user_id );
		$manager->destroy_all();
	}

	/**
	 * Log out only the oldest session for the user.
	 *
	 * This function retrieves the raw session tokens directly from user meta,
	 * identifies the oldest session by its login timestamp, and removes it.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 1.3.2
	 * @access private
	 *
	 * @return void
	 */
	private function logout_oldest_session( $user_id ) {
		// Retrieve the raw sessions array directly from user meta.
		$sessions = get_user_meta( $user_id, 'session_tokens', true );
		if ( ! is_array( $sessions ) || empty( $sessions ) ) {
			return;
		}
		$oldest_token = '';
		$oldest_time  = PHP_INT_MAX;
		// Loop through sessions to find the oldest one.
		foreach ( $sessions as $token => $session ) {
			if ( isset( $session['login'] ) && $session['login'] < $oldest_time ) {
				$oldest_time  = $session['login'];
				$oldest_token = $token;
			}
		}
		// Remove the oldest session directly.
		if ( $oldest_token ) {
			unset( $sessions[ $oldest_token ] );
			update_user_meta( $user_id, 'session_tokens', $sessions );
			$this->maybe_show_wc_notice();
		}
	}

	/**
	 * Check if the current user is allowed for another login.
	 *
	 * Count all the active logins for the current user annd
	 * check if that exceeds the maximum login limit set.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return boolean Limit reached or not
	 */
	private function reached_limit( $user_id ) {
		// If bypassed.
		if ( $this->bypass( $user_id ) ) {
			return false;
		}

		// Get maximum active logins allowed.
		$maximum = intval( get_option( 'loggedin_maximum', 1 ) );

		// Sessions token instance.
		$manager = WP_Session_Tokens::get_instance( $user_id );

		// Count sessions.
		$count = count( $manager->get_all() );

		// Check if limit reached.
		$reached = $count >= $maximum;

		/**
		 * Filter hook to change the limit condition.
		 *
		 * @param bool $reached Reached.
		 * @param int  $user_id User ID.
		 * @param int  $count   Active logins count.
		 *
		 * @since 1.3.0
		 * @since 1.3.1 Added count param.
		 */
		return apply_filters( 'loggedin_reached_limit', $reached, $user_id, $count );
	}

	/**
	 * Custom login limit bypassing.
	 *
	 * Filter to bypass login limit based on a condition.
	 * You can make use of this filter if you want to bypass
	 * some users or roles from limit limit.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function bypass( $user_id ) {
		/**
		 * Filter hook to bypass the check.
		 *
		 * @param bool $bypass  Bypassed.
		 * @param int  $user_id User ID.
		 *
		 * @since 1.0.0
		 */
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
		// Error message.
		$message = __( 'Maximum no. of active logins found for this account. Please logout from another device to continue.', 'loggedin' );

		/**
		 * Filter hook to change the error message.
		 *
		 * @param string $message Message.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'loggedin_error_message', $message );
	}


	/**
	 * Output a notice if user exceeded maximum amount of logins.
	 */
	public function maybe_show_wc_notice() {
		if ( function_exists( 'wc_add_notice' ) ) {
			// We need to manually set the customer session cookie
			// because WooCommerce only sets it at the beginning of the request.
			WC()->session->set_customer_session_cookie( true );
			wc_add_notice(
				__( 'The maximum number of active sessions for your account has been exceeded. Therefore, your oldest session has been terminated.', 'loggedin' ),
				'notice'
			);
		}
	}

}
