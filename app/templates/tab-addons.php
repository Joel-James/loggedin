<?php
/**
 * Addons page base template.
 *
 * @since      2.0.0
 *
 * @var array $addons            Addon list.
 * @var array $registered_addons Registered addons.
 * @var array $license_items     License items.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright  Copyright (c) 2025, Joel James
 * @package    View
 */

use DuckDev\Freemius\Services\Service;
use DuckDev\Loggedin\View;

?>
<br/>
<div class="widefat">
	<div id="the-list">
		<?php if ( ! empty( $addons ) ) : ?>
			<?php
			foreach ( $addons as $addon ) {
				View::render(
					'addons/addon-card',
					array(
						'id'                => $addon['id'],
						'title'             => $addon['title'] ?? '',
						'is_active'         => isset( $registered_addons[ $addon['id'] ] ),
						'is_license_active' => isset( $license_items[ $addon['id'] ]['status'] ) && $license_items[ $addon['id'] ]['status'] === Service::ACTIVATED,
						'license_key'       => $license_items[ $addon['id'] ]['key'] ?? '',
						'link'              => $addon['link'] ?? '',
						'is_premium'        => $addon['is_premium'] ?? false,
						'icon'              => $addon['icon'] ?? '',
						'description'       => $addon['info']['description'] ?? '',
						'homepage'          => $addon['info']['url'] ?? 'https://duckdev.com/',
					)
				);
			}
			?>
		<?php else : ?>
			<div class="notice notice-info inline">
				<p><?php esc_attr_e( 'No addons found.', 'loggedin' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
<div class="tablenav bottom">
	<p>
		<?php
		printf(
			// translators: Link to refresh action.
			esc_attr__( 'Missing an extension that you think you should be able to see? %1$sRefresh the list now%2$s', 'loggedin' ),
			'<a href="' . esc_url_raw( wp_nonce_url( add_query_arg( 'loggedin-addons-refresh', 1 ), 'loggedin-addons-refresh' ) ) . '">',
			'</a>'
		);
		?>
	</p>
</div>
