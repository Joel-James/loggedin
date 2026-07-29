<?php
/**
 * Tests for the WP-CLI module.
 *
 * The command classes themselves can't be exercised here — they call
 * into `WP_CLI`, which isn't loaded under the PHPUnit bootstrap. What
 * these tests do guard is the boundary: the module must be safe to
 * touch in a non-CLI context, because a fatal here would take down
 * every request on the site, not just `wp` invocations.
 *
 * @package DuckDev\Loggedin
 */

declare( strict_types = 1 );

use DuckDev\Loggedin\Cli\Commands;
use DuckDev\Loggedin\Cli\Sessions_Command;
use DuckDev\Loggedin\Cli\Settings_Command;

/**
 * @group cli
 */
class Loggedin_Cli_Test extends WP_UnitTestCase {

	public function test_wp_cli_is_absent_under_phpunit(): void {
		// Guards the premise of every other assertion in this file.
		$this->assertFalse(
			class_exists( 'WP_CLI' ),
			'WP_CLI is unexpectedly loaded — the rest of this test file assumes it is not.'
		);
	}

	public function test_registrar_is_a_noop_without_wp_cli(): void {
		// No exception, no fatal — the `class_exists` guard in
		// `Commands::init()` is the only thing standing between a web
		// request and a call to an undefined class.
		$this->assertInstanceOf( Commands::class, Commands::instance() );
	}

	public function test_command_classes_are_autoloadable(): void {
		// Catches a classmap that wasn't regenerated after the files
		// moved, and any parse-time dependency on a WP-CLI class.
		$this->assertTrue( class_exists( Sessions_Command::class ) );
		$this->assertTrue( class_exists( Settings_Command::class ) );
	}

	public function test_command_classes_expose_expected_subcommands(): void {
		// WP-CLI derives subcommand names from public methods, so a
		// rename is a breaking change to the public CLI surface.
		$this->assertTrue( method_exists( Sessions_Command::class, 'list_' ) );
		$this->assertTrue( method_exists( Sessions_Command::class, 'count' ) );
		$this->assertTrue( method_exists( Sessions_Command::class, 'destroy' ) );

		$this->assertTrue( method_exists( Settings_Command::class, 'list_' ) );
		$this->assertTrue( method_exists( Settings_Command::class, 'get' ) );
		$this->assertTrue( method_exists( Settings_Command::class, 'set' ) );
	}
}
