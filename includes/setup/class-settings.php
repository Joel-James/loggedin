<?php
/**
 * Settings store.
 *
 * Single source of truth for plugin settings. All callers go through
 * this class instead of `get_option()` / `update_option()` directly
 * so we can:
 *
 *   - Hide the option key behind a method (it's a constant on
 *     `Plugin`, but consumers shouldn't care).
 *   - Merge stored values with defaults on every read, so a partial
 *     payload (e.g. an addon writing only one key) never returns
 *     `null` for the unrelated keys.
 *   - Validate writes through a single `sanitize()` method that's
 *     also wired into `register_setting()`'s sanitize callback, so
 *     REST writes and direct `update_option()` writes both pass
 *     through the same validation.
 *
 * The option is registered with `show_in_rest`, which exposes it at
 * `/wp/v2/settings` and lets the React Settings tab read/write it
 * via `@wordpress/core-data`'s `useEntityProp`.
 *
 * @package FoxeLabs\Loggedin\Setup
 */

declare( strict_types = 1 );

namespace FoxeLabs\Loggedin\Setup;

use FoxeLabs\Loggedin\Contracts\Singleton;
use FoxeLabs\Loggedin\Plugin;

defined( 'WPINC' ) || die;

/**
 * Unified settings store backed by a single WP option.
 *
 * @since 3.0.0
 */
final class Settings {

	use Singleton;

	/**
	 * Modes the session guard knows how to enforce.
	 *
	 * Kept here so both the schema and the sanitizer derive from a
	 * single list.
	 *
	 * @since 3.0.0
	 */
	private const ALLOWED_LOGICS = array( 'allow', 'logout_oldest', 'block' );

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	protected function init(): void {
		// `init` (not `admin_init`) so the option is registered on
		// every request type — REST included. WordPress reads the
		// `show_in_rest` schema at REST controller boot, which fires
		// outside the admin context.
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Default values for every known setting key.
	 *
	 * Filterable so an addon can introduce new keys at runtime; once
	 * declared here they'll round-trip through `all()` / `update()`
	 * automatically.
	 *
	 * @since 3.0.0
	 *
	 * @return array{maximum: int, logic: string}
	 */
	public function defaults(): array {
		/**
		 * Filter the default settings payload.
		 *
		 * @since 3.0.0
		 *
		 * @param array $defaults Defaults keyed by setting id.
		 */
		return apply_filters(
			'loggedin_settings_defaults',
			array(
				'maximum' => 1,
				'logic'   => 'allow',
			)
		);
	}

	/**
	 * Return the full settings array merged with defaults.
	 *
	 * Stored values win; defaults backfill any key the stored array
	 * is missing. The merge order also means a write that drops a
	 * key won't silently revert that key to its default — the key
	 * would simply be missing from `$stored` and the default would
	 * step in.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		$stored = get_option( Plugin::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return array_merge( $this->defaults(), $stored );
	}

	/**
	 * Get a single setting value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key      Setting id.
	 * @param mixed  $fallback Returned when the key is missing.
	 *
	 * @return mixed
	 */
	public function get( string $key, $fallback = null ) {
		$all = $this->all();

		return $all[ $key ] ?? $fallback;
	}

	/**
	 * Replace the stored settings with a sanitized payload.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string, mixed> $values Raw input.
	 *
	 * @return bool True if WP wrote to the DB.
	 */
	public function update( array $values ): bool {
		return update_option( Plugin::OPTION_KEY, $this->sanitize( $values ) );
	}

	/**
	 * Register the option with WordPress (REST-exposed).
	 *
	 * The `show_in_rest` schema is what makes the option available at
	 * `/wp/v2/settings` for the React app. `additionalProperties =>
	 * false` is intentional — unknown keys are dropped at the REST
	 * boundary so an addon can't smuggle arbitrary fields into the
	 * site option via a crafted request.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		register_setting(
			'loggedin',
			Plugin::OPTION_KEY,
			array(
				'type'              => 'object',
				'default'           => $this->defaults(),
				'sanitize_callback' => array( $this, 'sanitize' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => false,
						'properties'           => array(
							'maximum' => array(
								'type'    => 'integer',
								'minimum' => 1,
							),
							'logic'   => array(
								'type' => 'string',
								'enum' => self::ALLOWED_LOGICS,
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Sanitize a settings payload.
	 *
	 * Used both as `register_setting()`'s sanitize callback and by
	 * `update()` so any write path produces the same shape.
	 *
	 * Unknown keys are silently dropped; known keys are coerced to
	 * the expected type and clamped to the allowed range.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $input Raw input.
	 *
	 * @return array<string, mixed>
	 */
	public function sanitize( $input ): array {
		$defaults = $this->defaults();

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$out = $defaults;

		if ( isset( $input['maximum'] ) ) {
			$out['maximum'] = max( 1, (int) $input['maximum'] );
		}

		if ( isset( $input['logic'] ) ) {
			$logic        = (string) $input['logic'];
			$out['logic'] = in_array( $logic, self::ALLOWED_LOGICS, true )
				? $logic
				: $defaults['logic'];
		}

		return $out;
	}
}
