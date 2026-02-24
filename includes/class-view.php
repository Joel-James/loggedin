<?php
/**
 * Template view manager class.
 *
 * @link       https://duckdev.com/products/loggedin-limit-active-logins/
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @category   Core
 * @package    Loggedin
 * @author     Joel James <me@joelsays.com>
 */

namespace DuckDev\Loggedin;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class View
 */
class View {

	/**
	 * Dummy initializer.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	protected function __construct() {
		// Nothing.
	}

	/**
	 * Render a template into a variable.
	 *
	 * This will look for the template file inside
	 * /app/templates/{file}.php
	 *
	 * @since 2.0.0
	 *
	 * @param string $file File path.
	 * @param array  $args Arguments.
	 * @param bool   $once Should include once.
	 *
	 * @return string
	 */
	public static function get_render( string $file, array $args = array(), bool $once = true ): string {
		ob_start();

		// Render the template.
		self::render( $file, $args, $once );

		return ob_get_clean();
	}

	/**
	 * Render a template.
	 *
	 * This will look for the template file inside
	 * /app/templates/{file}.php
	 *
	 * @since 2.0.0
	 *
	 * @param string $file     File path.
	 * @param array  $args     Arguments.
	 * @param bool   $once     Should include once.
	 * @param bool   $absolute Is absolute path.
	 *
	 * @return void
	 */
	public static function render( string $file, array $args = array(), bool $once = false, bool $absolute = false ) {
		// Full path to the file.
		if ( ! $absolute ) {
			$file = LOGGEDIN_DIR . "/app/templates/{$file}.php";
		}

		if ( file_exists( $file ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			if ( $once ) {
				include_once $file;
			} else {
				include $file;
			}
		}
	}
}
