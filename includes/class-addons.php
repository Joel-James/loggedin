<?php
/**
 * Addons functionality.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @package    Loggedin
 * @subpackage Freemius
 * @author     Joel James <me@joelsays.com>
 */

namespace DuckDev\Loggedin;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use DuckDev\Freemius\Freemius;

/**
 * Class Addons
 *
 * @since 1.0.0
 */
class Addons {

	/**
	 * Freemius ID of the addon.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	const FREEMIUS_ID = 19328;

	/**
	 * Freemius instance.
	 *
	 * @var Freemius[]
	 */
	protected array $freemius = array();

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
		// Initialise Freemius.
		add_action( 'admin_init', array( $this, 'init_freemius' ) );
		// Process license activate/deactivate.
		add_action( 'admin_init', array( $this, 'process_license' ) );
		// Process addon list refresh.
		add_action( 'admin_init', array( $this, 'process_addons_refresh' ) );
		// Add template vars.
		add_filter( 'loggedin_admin_page_vars', array( $this, 'admin_page_vars' ) );
	}

	/**
	 * Get the list of addons.
	 *
	 * Get the latest list from the Freemius API.
	 * It will be cached for 1 day.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $force Should force refresh.
	 *
	 * @return array
	 */
	public function get_addons( bool $force = false ): array {
		// Get addon list from the API.
		return $this->freemius[ self::FREEMIUS_ID ]->addon()->get_addons( $force );
	}

	/**
	 * Create new options field to the settings page.
	 *
	 * @since  2.0.0
	 * @uses   get_option() To get the option value.
	 *
	 * @return array
	 */
	public function get_license_items(): array {
		$items  = array();
		$addons = $this->get_registered_addons();

		foreach ( $addons as $id => $args ) {
			if ( isset( $this->freemius[ $id ] ) ) {
				$activation   = $this->freemius[ $id ]->license()->get_activation_data();
				$items[ $id ] = array(
					'key'    => $activation['activation_params']['license_key'] ?? '',
					'status' => $activation['status'] ?? '',
					'plugin' => $this->freemius[ $id ]->license()->get_plugin()->get_data(),
				);
			}
		}

		return $items;
	}

	/**
	 * Add addon related vars to template vars.
	 *
	 * @since  2.0.0
	 *
	 * @param array $vars Variables.
	 *
	 * @return array
	 */
	public function admin_page_vars( array $vars ): array {
		$addons = $this->get_addons();

		// If addons are empty, remove the tab.
		if ( empty( $addons ) ) {
			unset( $vars['tab_items']['addons'] );
			$vars['current_tab'] = 'settings';
		}

		$vars['addons']            = $addons;
		$vars['license_items']     = $this->get_license_items();
		$vars['registered_addons'] = $this->get_registered_addons();

		return $vars;
	}

	/**
	 * Process license activation and deactivation form submit.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function process_license() {
		// Form data.
		$data = wp_unslash( $_POST['loggedin_licenses'] ?? '' ); // phpcs:ignore

		// We need all these data to continue.
		if ( ! isset( $data['nonce'], $data['action'], $data['key'], $data['id'] ) ) {
			return;
		}

		// Nonce verification first.
		if ( ! wp_verify_nonce( $data['nonce'], 'loggedin_licenses[nonce]' ) ) {
			add_settings_error( 'loggedin_licenses', 'nonce_check_failed', __( 'Nonce check failed.', 'loggedin' ) );

			return;
		}

		// Now check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( 'loggedin_licenses', 'permission_check_failed', __( 'You do not have the permission to do this.', 'loggedin' ) );

			return;
		}

		// Prepare form data.
		$id     = intval( $data['id'] );
		$key    = sanitize_text_field( $data['key'] );
		$action = 'activate' === $data['action'] ? 'activate' : 'deactivate';

		// Do not continue if addon not initialised.
		if ( ! isset( $this->freemius[ $id ] ) ) {
			add_settings_error( 'loggedin_licenses', 'addon_not_initialized', __( 'Addon not initialized.', 'loggedin' ) );

			return;
		}

		if ( 'activate' === $action ) {
			$response = $this->freemius[ $id ]->license()->activate( $key );
		} else {
			$response = $this->freemius[ $id ]->license()->deactivate();
		}

		if ( is_wp_error( $response ) ) {
			// Show error notice.
			add_settings_error( 'loggedin_licenses', $response->get_error_code(), $response->get_error_message() );
		} else {
			// Show success notice.'
			add_settings_error(
				'loggedin_licenses',
				$action,
				'activate' === $action ? __( 'Your license key has been activated successfully.', 'loggedin' ) : __( 'Your license key has been deactivated.', 'loggedin' ),
				'updated'
			);
		}
	}

	/**
	 * Process addon list refresh request.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function process_addons_refresh() {
		// We need all these data to continue.
		if ( ! isset( $_REQUEST['_wpnonce'], $_REQUEST['loggedin-addons-refresh'] ) ) {
			return;
		}

		// Nonce verification first.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'loggedin-addons-refresh' ) ) {
			return;
		}

		// Now check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Force refresh addons.
		$this->get_addons( true );
	}

	/**
	 * Initialise Freemius SDK.
	 *
	 * This will create new Freemius instance for the parent plugin
	 * as well as for all registered addons.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function init_freemius(): void {
		// Initialize Freemius for Loggedin.
		$this->freemius[ self::FREEMIUS_ID ] = Freemius::get_instance(
			self::FREEMIUS_ID,
			array(
				'slug'       => 'loggedin',
				'is_premium' => false,
				'main_file'  => LOGGEDIN_FILE,
				'public_key' => 'pk_4c8df806d035c90805a97eae5fba5',
				'has_addons' => true,
			)
		);

		// Initialise Freemius for each addon.
		foreach ( $this->get_registered_addons() as $id => $args ) {
			$this->freemius[ $id ] = Freemius::get_instance( $id, $args );
		}
	}

	/**
	 * Get list of registered addons.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_registered_addons(): array {
		/**
		 * Get the registered addons.
		 *
		 * Addon plugins should use this filter to register it.
		 *
		 * @since 2.0.0
		 *
		 * @param array $addons Addon list.
		 */
		return apply_filters( 'loggedin_register_addon', array() );
	}
}
