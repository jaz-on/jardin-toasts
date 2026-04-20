<?php
/**
 * Scraper config.
 *
 * @package BeerJournal
 */

namespace BJ\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class ScraperConfigTest
 */
class ScraperConfigTest extends TestCase {

	/**
	 * Selector map is non-empty for each group.
	 *
	 * @return void
	 */
	public function test_dom_selectors_structure() {
		$s = BJ_Scraper_Config::dom_selectors();
		foreach ( array( 'beer', 'brewery', 'style', 'rating', 'comment', 'venue', 'photo' ) as $key ) {
			$this->assertArrayHasKey( $key, $s );
			$this->assertIsArray( $s[ $key ] );
			$this->assertNotEmpty( $s[ $key ] );
		}
	}

	/**
	 * Markup version is a positive int constant.
	 *
	 * @return void
	 */
	public function test_markup_version() {
		$this->assertGreaterThan( 0, \BJ_Scraper_Config::MARKUP_VERSION );
	}
}
