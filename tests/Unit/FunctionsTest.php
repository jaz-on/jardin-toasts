<?php
/**
 * Tests for helper functions (no WordPress runtime).
 *
 * @package BeerJournal
 */

namespace BJ\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class FunctionsTest
 */
class FunctionsTest extends TestCase {

	/**
	 * Check-in ID from URL.
	 *
	 * @return void
	 */
	public function test_parse_checkin_id_from_url() {
		$this->assertSame( '1527514863', bj_parse_checkin_id_from_url( 'https://untappd.com/user/foo/checkin/1527514863' ) );
		$this->assertNull( bj_parse_checkin_id_from_url( 'https://example.com/' ) );
	}

	/**
	 * RSS title parsing.
	 *
	 * @return void
	 */
	public function test_parse_rss_item_title() {
		$t = 'Jason is drinking a Meteor Blonde by Brasserie Meteor at Untappd at Home';
		$p = bj_parse_rss_item_title( $t );
		$this->assertSame( 'Meteor Blonde', $p['beer'] );
		$this->assertSame( 'Brasserie Meteor', $p['brewery'] );
		$this->assertSame( 'Untappd at Home', $p['venue'] );
	}
}
