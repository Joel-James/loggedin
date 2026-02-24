<?php
/**
 * Admin base template.
 *
 * @since      2.0.0
 *
 * @var array  $tab_items         Nav menu tab items.
 * @var string $current_tab       Current tab item.
 * @var array  $addons            Addon list.
 * @var int    $login_maximum     Maximum logins allowed.
 * @var string $login_logic       Login logic.
 * @var array  $registered_addons Registered addons.
 * @var array  $license_items     License items.
 * @var array  $logics            Loggedin logics.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright  Copyright (c) 2025, Joel James
 * @package    View
 */

use DuckDev\Loggedin\View;

?>
	<div class="wrap">
	<h2><strong>🔒 <?php esc_attr_e( 'Loggedin – Limit Concurrent Sessions', 'loggedin' ); ?></strong></h2>
	<?php settings_errors(); ?>
	<br/>
<?php
// Render menu.
View::render(
	'components/nav-menu',
	compact( 'tab_items', 'current_tab' )
);

// Render tab content.
if ( 'addons' === $current_tab && ! empty( $addons ) ) {
	View::render(
		'tab-addons',
		compact( 'addons', 'registered_addons', 'license_items' )
	);
} elseif ( 'support' === $current_tab ) {
	View::render( 'tab-support' );
} else {
	View::render(
		'tab-settings',
		compact( 'login_maximum', 'login_logic', 'logics' )
	);
}
