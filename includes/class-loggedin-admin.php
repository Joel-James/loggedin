<?php

// If this file is called directly, abort.
defined( 'WPINC' ) || die( 'Well, get lost.' );

/**
 * Admin side functionality of the plugin.
 *
 * @link       https://thefoxe.com/products/loggedin
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @category   Core
 * @package    Loggedin
 * @subpackage Admin
 * @author     Joel James <me@joelsays.com>
 */
class Loggedin_Admin {

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
		// Set options page.
		add_action( 'admin_init', array( $this, 'options_page' ) );
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
		// Register settings.
		register_setting( 'general', 'loggedin_maximum' );

		// Add new setting filed.
		add_settings_field(
			'loggedin_label',
			'<label for="dpr">' . __( 'Maximum Active Logins', 'loggedin' ) . '</label>',
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
		// Get settings value.
		$value = get_option( 'loggedin_maximum', 3 );

		echo '<input type="number" name="loggedin_maximum" min="1" value="' . intval( $value ) . '" />';
		echo '<p class="description">' . __( 'Set the maximum no. of active logins a user account can have.', 'loggedin' ) . '</p>';
		echo '<p class="description">' . __( 'If this limit reached, next login request will be failed and user will have to logout from one device to continue.', 'loggedin' ) . '</p>';
		echo '<p class="description"><strong>' . __( 'Note: ', 'loggedin' ) . '</strong>' . __( 'Even if the browser is closed, login session may exist.', 'loggedin' ) . '</p>';
	}

	/**
	 * Show admin to ask for review in wp.org.
	 *
	 * Show admin notice only inside our plugin's settings page.
	 * Hide the notice permanently if user dismissed it.
	 *
	 * @since 1.1.0
	 *
	 * @return void|bool
	 */
	public function review_notice() {
		global $pagenow;

		// Only on our settings page.
		if ( 'options-general.php' === $pagenow ) {
			// Only for admins.
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			// Get the notice time.
			$notice_time = get_option( 'i4t3_review_notice' );

			// If not set, set now and bail.
			if ( ! $notice_time ) {
				// Set to next week.
				return add_option( 'i4t3_review_notice', time() + 604800 );
			}

			// Current logged in user.
			$current_user = wp_get_current_user();

			// Did the current user already dismiss?.
			$dismissed = get_user_meta( $current_user->ID, 'i4t3_review_notice_dismissed', true );

			// Continue only when allowed.
			if ( (int) $notice_time <= time() && ! $dismissed ) {
				?>
                <div class="notice notice-success">
                    <p><?php printf( __( 'Hey %1$s, I noticed you\'ve been using %2$s404 to 301%3$s for more than 1 week – that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.', '404-to-301' ),
							empty( $current_user->display_name ) ? __( 'there', '404-to-301' ) : ucwords( $current_user->display_name ),
							'<strong>',
							'</strong>'
						); ?>
                    </p>
                    <p>
                        <a href="https://wordpress.org/support/plugin/404-to-301/reviews/#new-post"
                           target="_blank"><?php esc_html_e( 'Ok, you deserve it', '404-to-301' ); ?></a>
                    </p>
                    <p>
                        <a href="<?php echo add_query_arg( 'jj4t3_rating', 'later' ); // later. ?>"><?php esc_html_e( 'Nope, maybe later', '404-to-301' ); ?></a>
                    </p>
                    <p>
                        <a href="<?php echo add_query_arg( 'jj4t3_rating', 'dismiss' ); // dismiss link. ?>"><?php esc_html_e( 'I already did', '404-to-301' ); ?></a>
                    </p>
                </div>
				<?php
			}
		}
	}

	/**
	 * Handle review notice actions.
	 *
	 * If dismissed set a user meta for the current user and do not show again.
	 * If agreed to review later, update the review timestamp to after 2 weeks.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function review_action() {
		// Get the current review action.
		$action = isset( $_REQUEST['loggedin_rating'] ) ? $_REQUEST['loggedin_rating'] : '';

		switch ( $action ) {
			case 'later':
				// Let's show after another 2 weeks.
				update_option( 'loggedin_rating_notice', time() + 1209600 );
				break;
			case 'dismiss':
				// Do not show again to this user.
				update_user_meta( get_current_user_id(), 'loggedin_rating_notice_dismissed', 1 );
				break;
		}
	}
}
