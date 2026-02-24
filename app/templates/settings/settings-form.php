<?php
/**
 * Settings form template.
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

?>
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row">
			<label for="loggedin_maximum"><?php esc_attr_e( 'Active Logins Limit', 'loggedin' ); ?></label>
		</th>
		<td>
			<p>
				<input
					type="number"
					name="loggedin_maximum"
					id="loggedin_maximum"
					min="1"
					value="<?php echo intval( $login_maximum ); ?>"
					placeholder="<?php esc_html_e( 'Enter the limit in number', 'loggedin' ); ?>"
				/>
			</p>
			<p class="description"><?php esc_html_e( 'Set the maximum number of active logins allowed per user account.', 'loggedin' ); ?></p>
			<p class="description"><strong><?php esc_html_e( 'Note: ', 'loggedin' ); ?></strong><?php esc_html_e( 'Closing a browser may not terminate a login session.', 'loggedin' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="loggedin_logic"><?php esc_attr_e( 'Login Logic', 'loggedin' ); ?></label>
		</th>
		<td>
			<?php foreach ( $logics as $logic => $labels ): ?>
				<p>
					<input
						type="radio"
						name="loggedin_logic"
						id="loggedin_logic"
						value="<?php echo esc_attr( $logic ); ?>"
						<?php checked( $login_logic, $logic ); ?>
					/> <?php echo esc_attr( $labels['label'] ) ?? ''; ?>
				</p>
				<p class="description"><?php echo esc_attr( $labels['desc'] ) ?? ''; ?></p>
			<?php endforeach; ?>
		</td>
	</tr>
	</tbody>
</table>
