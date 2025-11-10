<?php
/**
 * Addon card template.
 *
 * @since      2.0.0
 *
 * @var int    $id                Addon ID.
 * @var string $title             Addon name.
 * @var string $icon              Addon icon url.
 * @var string $link              Addon page link.
 * @var string $description       Addon description.
 * @var string $license_key       License key.
 * @var bool   $is_premium        Is premium.
 * @var bool   $is_active         Is addon active.
 * @var bool   $is_license_active Is license active.
 * @var string $homepage          Home page URL.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright  Copyright (c) 2025, Joel James
 * @package    View
 */

use DuckDev\Loggedin\View;

?>
<div class="plugin-card">
	<div class="plugin-card-top">
		<div class="name column-name">
			<h3>
				<a href="<?php echo esc_url( $homepage ); ?>" target="_blank">
					<?php echo esc_html( $title ); ?>
					<img src="<?php echo esc_url( $icon ); ?>" class="plugin-icon" alt="<?php echo esc_html( $title ); ?>">
				</a>
			</h3>
		</div>
		<div class="action-links">
			<ul class="plugin-action-buttons">
				<li>
					<?php if ( $is_active ) : ?>
						<button class="button" disabled><?php esc_attr_e( 'Active', 'loggedin' ); ?></button>
					<?php else : ?>
						<a
							class="install-now button"
							href="<?php echo esc_url( $link ); ?>"
							aria-label="<?php esc_attr_e( 'Buy Now', 'loggedin' ); ?>"
							role="button"
							target="_blank"
						>
							<?php esc_attr_e( 'Buy Now', 'loggedin' ); ?>
						</a>
					<?php endif; ?>
				</li>
			</ul>
		</div>
		<div class="desc column-description">
			<p><?php echo esc_html( $description ); ?></p>
			<p class="authors"><cite>By <a href="https://profiles.wordpress.org/joelcj91/" target="_blank">Joel James</a></cite></p>
		</div>
	</div>
	<?php if ( $is_active ) : ?>
		<div class="plugin-card-bottom">
			<?php
			View::render(
				'addons/license-form',
				compact( 'id', 'license_key', 'is_license_active' )
			);
			?>
		</div>
	<?php else : ?>
		<div class="plugin-card-bottom">
			<div class="column-rating"><strong><?php $is_premium ? esc_attr_e( 'Premium', 'loggedin' ) : esc_attr_e( 'Free', 'loggedin' ); ?></strong></div>
			<div class="column-updated">
				<a href="<?php echo esc_url_raw( $homepage ); ?>" target="_blank"><?php esc_attr_e( 'More Details', 'loggedin' ); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>
