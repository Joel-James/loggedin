<?php
/**
 * Tests for {@see \FoxeLabs\Loggedin\Setup\Settings}.
 *
 * @package FoxeLabs\Loggedin
 */

declare( strict_types = 1 );

use FoxeLabs\Loggedin\Plugin;
use FoxeLabs\Loggedin\Setup\Settings;

/**
 * @group settings
 */
class Loggedin_Settings_Test extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( Plugin::OPTION_KEY );
		delete_option( 'loggedin_maximum' );
		delete_option( 'loggedin_logic' );

		parent::tear_down();
	}

	public function test_defaults_have_expected_keys(): void {
		$defaults = Settings::instance()->defaults();

		$this->assertSame( 1, $defaults['maximum'] );
		$this->assertSame( 'allow', $defaults['logic'] );
	}

	public function test_update_and_get_round_trip(): void {
		Settings::instance()->update(
			array(
				'maximum' => 5,
				'logic'   => 'block',
			)
		);

		$this->assertSame( 5, Settings::instance()->get( 'maximum' ) );
		$this->assertSame( 'block', Settings::instance()->get( 'logic' ) );
	}

	public function test_sanitize_rejects_invalid_logic(): void {
		$result = Settings::instance()->sanitize(
			array(
				'maximum' => 0,
				'logic'   => 'nonsense',
			)
		);

		$this->assertSame( 1, $result['maximum'] );
		$this->assertSame( 'allow', $result['logic'] );
	}

	public function test_all_merges_partial_stored_with_defaults(): void {
		update_option( Plugin::OPTION_KEY, array( 'maximum' => 3 ) );

		$all = Settings::instance()->all();

		$this->assertSame( 3, $all['maximum'] );
		$this->assertSame( 'allow', $all['logic'] );
	}
}
