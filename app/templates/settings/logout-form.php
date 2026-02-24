<?php
/**
 * Logout form template.
 *
 * @since      2.0.0
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @author     Joel James <me@joelsays.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright  Copyright (c) 2025, Joel James
 * @package    View
 */

?>
<hr/>
<h2><?php esc_attr_e( 'Manage Sessions', 'loggedin' ); ?></h2>
<p><?php esc_attr_e( 'Manage active user sessions.', 'loggedin' ); ?></p>
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row">
			<label for="loggedin_logout"><?php esc_attr_e( 'Force Logout', 'loggedin' ); ?></label>
		</th>
		<td>
			<input
				type="number"
				name="loggedin_user"
				id="loggedin_logout"
				min="1"
				placeholder="<?php esc_html_e( 'Enter user ID', 'loggedin' ); ?>"
			/>
			<input type="submit" name="loggedin_logout" class="button" value="<?php esc_html_e( 'Force Logout', 'loggedin' ); ?>"/>
			<p class="description"><?php esc_html_e( 'To force a user to log out from all devices, enter their User ID.', 'loggedin' ); ?></p>
		</td>
	</tr>
	</tbody>
</table>
