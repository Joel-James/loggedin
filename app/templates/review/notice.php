<?php
/**
 * Review notice template.
 *
 * @since      2.0.0
 *
 * @var WP_User $current_user Current user object.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright  Copyright (c) 2025, Joel James
 * @package    View
 */

?>
<div class="notice notice-success">
	<p>
		<?php
		printf(
		// translators: %1$s Current user's name. %2$s Plugin name.
			esc_html__( 'Hey %1$s, I noticed you\'ve been using %2$s plugin for more than 1 week – that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.', 'loggedin' ),
			empty( $current_user->display_name ) ? esc_html__( 'there', 'loggedin' ) : esc_html( ucwords( $current_user->display_name ) ),
			'<strong>Loggedin - Limit Active Logins</strong>'
		);
		?>
	</p>
	<p>
		<a href="https://wordpress.org/support/plugin/loggedin/reviews/#new-post" target="_blank">
			<?php esc_html_e( 'Ok, you deserve it', 'loggedin' ); ?>
		</a>
	</p>
	<p>
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'loggedin_rating', 'later' ), 'loggedin_rating' ) ); ?>">
			<?php esc_html_e( 'Nope, maybe later', 'loggedin' ); ?>
		</a>
	</p>
	<p>
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'loggedin_rating', 'dismiss' ), 'loggedin_rating' ) ); ?>">
			<?php esc_html_e( 'I already did', 'loggedin' ); ?>
		</a>
	</p>
</div>
