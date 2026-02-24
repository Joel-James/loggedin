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

namespace DuckDev\Loggedin;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_Error;
use WP_Session_Tokens;

/**
 * Class Core.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * Initialize the class and set its properties.
	 *
	 * We register all our common hooks here.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @param boolean $check    User Object/WPError.
	 * @param string  $password Plaintext user's password.
	 * @param string  $hash     Hash of the user's password to check against.
	 * @param int     $user_id  User ID.
	 *
	 * @return bool
	 */
	public function validate_allow_logic( $check, $password, $hash, $user_id ): bool {
		// If the validation failed already, bail.
		if ( ! $check ) {
			return false;
		}

		// Get current logic.
		$logic = get_option( 'loggedin_logic', 'allow' );

		if ( in_array( $logic, array( 'allow', 'logout_oldest' ) ) ) {
			// Continue only if limit reached.
			if ( $this->has_limit_reached( $user_id ) ) {
				if ( 'allow' === $logic ) {
					// Destroy all others.
					$this->destroy_all_sessions( $user_id );
				} elseif ( 'logout_oldest' === $logic ) {
					// Destroy oldest session.
					$this->destroy_oldest_session( $user_id );
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
	 * @since 1.0.0
	 *
	 * @param object $user User Object/WPError.
	 *
	 * @return object User object or error object.
	 */
	public function validate_block_logic( $user ) {
		// If login validation failed already, return that error.
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$logic = get_option( 'loggedin_logic', 'allow' );

		// Only when block method.
		if ( 'block' === $logic ) {
			// Check if limit exceed.
			if ( $this->has_limit_reached( $user->ID ) ) {
				/**
				 * Action hook to trigger when a login is blocked by loggedin.
				 *
				 * @since 2.0.0
				 *
				 * @param int $user_id User ID.
				 */
				do_action( 'loggedin_login_blocked', $user->ID );

				return new WP_Error( 'login_limit_reached', $this->limit_error_message() );
			}
		}

		return $user;
	}

	/**
	 * Destroy all sessions of the user.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	protected function destroy_all_sessions( $user_id ) {
		// Destroy all sessions.
		WP_Session_Tokens::get_instance( $user_id )->destroy_all();

		/**
		 * Action hook to trigger when all login sessions of a user are cleared by loggedin.
		 *
		 * @since 2.0.0
		 *
		 * @param int $user_id User ID.
		 */
		do_action( 'loggedin_destroy_all_sessions', $user_id );
	}

	/**
	 * Log out only the oldest session for the user.
	 *
	 * This function retrieves the raw session tokens directly from user meta,
	 * identifies the oldest session by its login timestamp, and removes it.
	 * This will not work when a different type of session storage (eg: Redis) is being used.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	protected function destroy_oldest_session( $user_id ) {
		// Retrieve the raw sessions array directly from user meta.
		$sessions = get_user_meta( $user_id, 'session_tokens', true );
		if ( ! is_array( $sessions ) || empty( $sessions ) ) {
			return;
		}

		$oldest_token = '';
		$oldest_time  = time();

		// Loop through sessions to find the oldest one.
		foreach ( $sessions as $token => $session ) {
			if ( isset( $session['login'] ) && $session['login'] < $oldest_time ) {
				$oldest_time  = $session['login'];
				$oldest_token = $token;
			}
		}

		if ( ! empty( $oldest_token ) ) {
			// Destroy oldest session.
			unset( $sessions[ $oldest_token ] );
			update_user_meta( $user_id, 'session_tokens', $sessions );

			/**
			 * Action hook to trigger when oldest login session of a user are cleared by loggedin.
			 *
			 * @since 2.0.0
			 *
			 * @param int $user_id User ID.
			 */
			do_action( 'loggedin_destroy_oldest_session', $user_id );
		}
	}

	/**
	 * Check if the current user is allowed for another login.
	 *
	 * Count all the active logins for the current user annd
	 * check if that exceeds the maximum login limit set.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 *
	 * @return boolean Limit reached or not
	 */
	protected function has_limit_reached( $user_id ): bool {
		// No user ID, no limit.
		if ( empty( $user_id ) ) {
			return false;
		}

		// If bypassed.
		if ( $this->is_bypassed( $user_id ) ) {
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
		 * @since 1.3.0
		 * @since 1.3.1 Added count param.
		 *
		 * @param bool $reached Reached.
		 * @param int  $user_id User ID.
		 * @param int  $count   Active logins count.
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
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool
	 */
	protected function is_bypassed( $user_id ): bool {
		/**
		 * Filter hook to bypass the check.
		 *
		 * @since 1.0.0
		 *
		 * @param int  $user_id User ID.
		 * @param bool $bypass  Bypassed.
		 */
		return (bool) apply_filters( 'loggedin_bypass', false, $user_id );
	}

	/**
	 * Error message text if user active logins count is maximum
	 *
	 * @since 1.0.0
	 *
	 * @return string Error message
	 */
	protected function limit_error_message(): string {
		// Error message.
		$message = __( 'You\'ve reached the maximum number of active logins for this account. Please log out from another device to continue.', 'loggedin' );

		/**
		 * Filter hook to change the error message.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message Message.
		 */
		return apply_filters( 'loggedin_error_message', $message );
	}
}
