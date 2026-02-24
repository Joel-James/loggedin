<?php
/**
 * Addon license form template.
 *
 * @since      2.0.0
 *
 * @var int    $id                Addon ID.
 * @var string $license_key       License key.
 * @var bool   $is_license_active Is license active.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright  Copyright (c) 2025, Joel James
 * @package    View
 */

?>

<form action="" method="post">
	<div class="column-rating" style="width: 80%; max-width: 80%;">
		<?php wp_nonce_field( "loggedin_licenses[nonce]", "loggedin_licenses[nonce]" ); ?>
		<input
			type="hidden"
			name="loggedin_licenses[id]"
			value="<?php echo intval( $id ); ?>"
		>
		<input
			type="hidden"
			name="loggedin_licenses[action]"
			value="<?php echo $is_license_active ? 'deactivate' : 'activate'; ?>"
		>
		<input
			type="password"
			class="large-text"
			name="loggedin_licenses[key]"
			id="loggedin_licenses[key]"
			aria-label="<?php esc_html_e( 'License Key', 'loggedin' ); ?>"
			placeholder="<?php esc_html_e( 'Enter license key', 'loggedin' ); ?>"
			value="<?php echo esc_html( $license_key ); ?>"
			<?php wp_readonly( $is_license_active ); ?>
		>
	</div>
	<div class="column-updated" style="width: 20%; max-width: 20%;">
		<?php if ( $is_license_active ) : ?>
			<button class="button" style="width: 85%;">
				<?php esc_attr_e( 'Deactivate', 'loggedin' ); ?>
			</button>
		<?php else : ?>
			<button class="button button-primary" style="width: 85%;">
				<?php esc_attr_e( 'Activate', 'loggedin' ); ?>
			</button>
		<?php endif; ?>
	</div>
</form>

