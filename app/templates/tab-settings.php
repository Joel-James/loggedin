<?php
/**
 * Settings page base template.
 *
 * @since      2.0.0
 *
 * @var int    $login_maximum Maximum logins allowed.
 * @var string $login_logic   Login logic.
 * @var array  $logics        Loggedin logics.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright  Copyright (c) 2025, Joel James
 * @package    View
 */

use DuckDev\Loggedin\View;

?>
<form action="options.php" method="post">
	<?php settings_fields( 'loggedin' ); ?>
	<h2><?php esc_attr_e( 'General Settings', 'loggedin' ); ?></h2>
	<p><?php esc_attr_e( 'Manage the plugin\'s general settings.', 'loggedin' ); ?></p>
	<?php
	// Settings form.
	View::render(
		'settings/settings-form',
		compact( 'login_maximum', 'login_logic', 'logics' )
	);

	/**
	 * Action hook to add more content at the bottom of settings section.
	 *
	 * @since 2.0.0
	 */
	do_action( 'loggedin_settings_bottom' );

	// Logout form.
	View::render( 'settings/logout-form' );

	submit_button( __( 'Save Settings', 'loggedin' ) );
	?>
</form>
