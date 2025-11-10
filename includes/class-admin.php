<?php
/**
 * Admin side functionality of the plugin.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @package    Loggedin
 * @subpackage Admin
 * @author     Joel James <me@joelsays.com>
 */

namespace DuckDev\Loggedin;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_Session_Tokens;

/**
 * Class Admin
 *
 * @since 1.0.0
 */
class Admin {

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
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'old_options_page' ) );

		// Process the login action.
		add_action( 'admin_init', array( $this, 'force_logout' ) );

		// Show review request.
		add_action( 'admin_notices', array( $this, 'review_notice' ) );
		add_action( 'admin_init', array( $this, 'review_action' ) );
	}

	/**
	 * Process the force logout action.
	 *
	 * This will force logout the user from all devices.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function force_logout() {
		// If force logout submit.
		if ( isset( $_REQUEST['loggedin_logout'] ) && isset( $_REQUEST['loggedin_user'] ) ) {
			// Security check.
			check_admin_referer( 'general-options' );

			// Get user.
			$user = get_userdata( (int) $_REQUEST['loggedin_user'] );

			if ( $user ) {
				// Sessions token instance.
				$manager = WP_Session_Tokens::get_instance( $user->ID );

				// Destroy all sessions.
				$manager->destroy_all();

				// Add success message.
				add_settings_error(
					'general',
					'settings_updated', // Override the settings update message.
					sprintf(
					// translators: %s User name of the logging out user.
						__( 'User %s forcefully logged out from all devices.', 'loggedin' ),
						$user->user_login
					),
					'updated'
				);
			} else {
				// Add success message.
				add_settings_error(
					'general',
					'settings_updated', // Override the settings update message.
					sprintf(
					// translators: %d User ID of the login user.
						__( 'Invalid user ID: %d', 'loggedin' ),
						intval( $_REQUEST['loggedin_user'] )
					)
				);
			}
		}
	}

	/**
	 * Register admin menu for the plugin.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_menu() {
		add_users_page(
		// translators: %s lock icon.
			sprintf( __( '%s Loggedin Settings', 'loggedin' ), '🔒' ),
			// translators: %s lock icon.
			sprintf( __( '%s Loggedin', 'loggedin' ), '<span class="dashicons dashicons-lock"></span>' ),
			'manage_options',
			'loggedin',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Register settings for plugin.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_settings() {
		// Register limit settings.
		register_setting(
			'loggedin',
			'loggedin_maximum',
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);

		// Register logic settings.
		register_setting(
			'loggedin',
			'loggedin_logic',
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);
	}

	/**
	 * Create new option field label to the default settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_page() {
		// Admin template vars.
		$vars = array(
			'login_maximum' => get_option( 'loggedin_maximum', 3 ),
			'login_logic'   => get_option( 'loggedin_logic', 'allow' ),
			'current_tab'   => $this->get_current_tab(),
			'logics'        => $this->loggedin_logics(),
			'tab_items'     => array(
				'settings' => array(
					'label' => __( 'Settings', 'loggedin' ),
					'icon'  => 'dashicons-admin-settings',
				),
				'addons'   => array(
					'label' => __( 'Addons', 'loggedin' ),
					'icon'  => 'dashicons-screenoptions',
				),
				'support'  => array(
					'label' => __( 'Support', 'loggedin' ),
					'icon'  => 'dashicons-editor-help',
				),
			),
		);

		/**
		 * Filter to modify admin template vars.
		 *
		 * @since 2.0.0
		 *
		 * @param array $vars Variables.
		 */
		$vars = apply_filters( 'loggedin_admin_page_vars', $vars );

		View::render( 'admin', $vars );
	}

	/**
	 * Create new option field for old settings section.
	 *
	 * @since     1.0.0
	 * @uses      add_settings_field() To add new field to for the setting.
	 * @depecated 2.0.0
	 *
	 * @return void
	 */
	public function old_options_page() {
		add_settings_section(
			'loggedin_settings',
			// translators: %s lock icon.
			sprintf( __( '%s Loggedin Settings', 'loggedin' ), '<span class="dashicons dashicons-lock"></span>' ),
			array( $this, 'loggedin_old_settings' ),
			'general'
		);
	}

	/**
	 * Old settings page section content.
	 *
	 * @since     1.0.0
	 * @depecated 2.0.0
	 *
	 * @return void
	 */
	public function loggedin_old_settings() {
		?>
		<p class="description">
			<?php
			printf(
			// translators: Link to loggedin settings page url.
				esc_attr__( 'Loggedin settings have been relocated. %1$sClick here%2$s to access the new settings page.', 'loggedin' ),
				'<a href="' . esc_url( admin_url( 'users.php?page=loggedin' ) ) . '">',
				'</a>'
			);
			?>
		</p>
		<?php
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
		$current_screen = get_current_screen();

		// Only on our settings page.
		if ( isset( $current_screen->id ) && 'users_page_loggedin' === $current_screen->id ) {
			// Only for admins.
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			// Get the notice time.
			$notice_time = get_option( 'loggedin_rating_notice' );

			// If not set, set now and bail.
			if ( empty( $notice_time ) ) {
				// Set to next week.
				return add_option( 'loggedin_rating_notice', time() + 604800 );
			}

			// Current logged in user.
			$current_user = wp_get_current_user();

			// Did the current user already dismiss?.
			$dismissed = get_user_meta( $current_user->ID, 'loggedin_rating_notice_dismissed', true );

			// Continue only when allowed.
			if ( (int) $notice_time <= time() && ! $dismissed ) {
				View::render(
					'review/notice',
					array( 'current_user' => $current_user ),
				);
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
		// Only for admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Nonce verification.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'loggedin_rating' ) ) {  // phpcs:ignore
			return;
		}

		// Get the current review action.
		$action = $_REQUEST['loggedin_rating'] ?? ''; // phpcs:ignore

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

	/**
	 * Get current tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_current_tab(): string {
		$tabs = array( 'settings', 'addons', 'support' );
		// phpcs:ignore
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';

		return in_array( $tab, $tabs, true ) ? $tab : 'settings';
	}

	/**
	 * List of logics being used by loggedin.
	 *
	 * Third party plugins and addons can use the filter to add new logics.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function loggedin_logics(): array {
		$logics = array(
			'logout_oldest' => array(
				'label' => __( 'Logout Oldest', 'loggedin' ),
				'desc'  => esc_html__( 'When the concurrent login limit is reached, a new login will automatically end the single oldest active session. This feature works only with user meta session storage.', 'loggedin' ),
			),
			'allow'         => array(
				'label' => __( 'Logout All', 'loggedin' ),
				'desc'  => esc_html__( 'When the concurrent login limit is reached, a new login will automatically terminate all previously active sessions.', 'loggedin' ),
			),
			'block'         => array(
				'label' => __( 'Block New', 'loggedin' ),
				'desc'  => esc_html__( 'If the concurrent login limit is reached, do not allow new logins. Users must then wait for existing login sessions to expire.', 'loggedin' ),
			),
		);

		/**
		 * Logged logics.
		 *
		 * @since 2.0.0
		 *
		 * @param array $logics Logics.
		 */
		return apply_filters( 'loggedin_logics', $logics );
	}
}
