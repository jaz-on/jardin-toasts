<?php
/**
 * Scraper DOM configuration and markup version (bump when Untappd HTML strategy changes).
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Scraper_Config
 */
class BJ_Scraper_Config {

	/**
	 * Increment when check-in page parsing strategy changes materially (for logs / support).
	 */
	public const MARKUP_VERSION = 1;

	/**
	 * DOM selector groups for check-in HTML (filtered).
	 *
	 * @return array<string, array<int, string>>
	 */
	public static function dom_selectors() {
		$selectors = array(
			'beer'    => array( '.beer-details h2', 'h1', '.name h1' ),
			'brewery' => array( '.beer-details p.brewery', '.brewery', 'a.brewery' ),
			'style'   => array( '.beer-details .style', '.beer-style', 'p.style' ),
			'rating'  => array( '.rating-serving .rating', '.rating', '[data-rating]' ),
			'comment' => array( '.checkin-comment', '.comment-text' ),
			'venue'   => array( '.venue-name', '.top-location' ),
			'photo'   => array( '.photo img', '.label img' ),
		);
		$filtered = apply_filters( 'bj_scraper_dom_selectors', $selectors );
		return is_array( $filtered ) ? $filtered : $selectors;
	}
}
