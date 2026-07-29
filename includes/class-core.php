<?php
/**
 * Plugin bootstrap.
 *
 * `Core` is the singleton entry point invoked from `loggedin.php`.
 * Its only job is to wire up each module in a predictable order so
 * downstream modules can rely on their dependencies being available.
 *
 * Boot phases:
 *   1. {@see common()} — Settings store + Upgrader. Always loaded so
 *      REST and front-end requests can read settings.
 *   2. {@see front()} — Session guard hooked into the auth pipeline.
 *      Always loaded; front-of-site logins happen outside `is_admin()`.
 *   3. {@see admin()} — wp-admin only: menu, page, asset enqueue.
 *   4. {@see addons()} — Freemius wiring. Loaded everywhere; the
 *      Freemius instances themselves are built lazily so non-admin
 *      requests don't pay for them unless a REST endpoint asks.
 *   5. {@see api()} — REST controllers. Always loaded; they only
 *      register routes on `rest_api_init`.
 *   6. {@see cli()} — WP-CLI commands. Only wired on CLI requests, so
 *      web traffic never loads the command classes.
 *
 * The `loggedin_init` action fires once boot completes so add-ons can
 * register their own modules with the same lifecycle.
 *
 * @package DuckDev\Loggedin
 */

declare( strict_types = 1 );

namespace DuckDev\Loggedin;

use DuckDev\Loggedin\Addons\Addons;
use DuckDev\Loggedin\Admin\Admin;
use DuckDev\Loggedin\Admin\Assets;
use DuckDev\Loggedin\Api\Addons as Addons_Api;
use DuckDev\Loggedin\Api\Sessions as Sessions_Api;
use DuckDev\Loggedin\Api\Settings as Settings_Api;
use DuckDev\Loggedin\Cli\Commands;
use DuckDev\Loggedin\Contracts\Singleton;
use DuckDev\Loggedin\Front\Session_Guard;
use DuckDev\Loggedin\Setup\Settings;
use DuckDev\Loggedin\Setup\Upgrader;

defined( 'WPINC' ) || die;

/**
 * Plugin bootstrap — wires every module and fires the public boot action.
 *
 * @since 3.0.0
 */
final class Core {

	use Singleton;

	/**
	 * Wire up every module and fire the public boot action.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function init(): void {
		$this->common();
		$this->front();
		$this->admin();
		$this->addons();
		$this->api();
		$this->cli();

		/**
		 * Fires once every module has been wired up.
		 *
		 * Add-ons should hook here to register themselves — by this
		 * point the Settings store and the REST namespace are
		 * already in place, so an add-on can safely call into either
		 * during its own `init`.
		 *
		 * @since 1.3.1
		 *
		 * @param Core $core The shared `Core` instance.
		 */
		do_action( 'loggedin_init', $this );
	}

	/**
	 * Modules required on every request type.
	 */
	private function common(): void {
		Settings::instance();
		Upgrader::instance();
	}

	/**
	 * Front-end modules — runs on every page load, including the
	 * wp-login flow.
	 */
	private function front(): void {
		Session_Guard::instance();
	}

	/**
	 * Admin-only modules. Gated on `is_admin()` so the menu /
	 * asset registrations don't fire on REST or front-end requests.
	 */
	private function admin(): void {
		if ( is_admin() ) {
			Admin::instance();
			Assets::instance();
		}
	}

	/**
	 * Freemius wiring.
	 *
	 * Loaded everywhere — REST endpoints (which run outside
	 * `is_admin()`) need it too. The Addons module defers the actual
	 * `Freemius::get_instance()` calls until the first read, so this
	 * line is cheap on non-admin requests.
	 */
	private function addons(): void {
		Addons::instance();
	}

	/**
	 * REST controllers. Each registers its routes on `rest_api_init`,
	 * so calling `instance()` here outside of a REST context is
	 * effectively free.
	 */
	private function api(): void {
		Settings_Api::instance();
		Addons_Api::instance();
		Sessions_Api::instance();
	}

	/**
	 * WP-CLI commands.
	 *
	 * Gated on the `WP_CLI` constant so the command classes are never
	 * autoloaded on a web request — the CLI surface costs a stock
	 * install exactly nothing.
	 */
	private function cli(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			Commands::instance();
		}
	}
}
