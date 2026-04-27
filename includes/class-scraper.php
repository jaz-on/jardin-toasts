<?php
/**
 * HTML scraping for Untappd check-in pages.
 *
 * @package BeerJournal
 */

use Symfony\Component\DomCrawler\Crawler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Scraper
 */
class BJ_Scraper {

	/**
	 * Max retries per URL.
	 */
	private const MAX_ATTEMPTS = 3;

	/**
	 * Scrape a check-in URL and return normalized data.
	 *
	 * @param string $url Check-in URL.
	 * @return array<string, mixed>|WP_Error
	 */
	public function scrape_checkin_url( $url ) {
		$url = esc_url_raw( $url );
		if ( ! $url || false === strpos( $url, 'untappd.com' ) ) {
			return new WP_Error( 'bad_url', __( 'Invalid Untappd check-in URL.', 'beer-journal' ) );
		}

		$this->respect_rate_limit( bj_get_scraping_delay_seconds() );

		$attempts = 0;
		$html     = '';
		$code     = 0;
		while ( $attempts < self::MAX_ATTEMPTS ) {
			++$attempts;
			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 20,
					'headers' => array(
						'Accept' => 'text/html,application/xhtml+xml',
					),
					'user-agent' => apply_filters(
						'bj_http_user_agent',
						'Beer Journal/' . BJ_VERSION . '; ' . home_url( '/' )
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				BJ_Logger::warning( 'Scrape attempt ' . $attempts . ' failed: ' . $response->get_error_message() );
				sleep( min( 2 * $attempts, 10 ) );
				continue;
			}

			$code = wp_remote_retrieve_response_code( $response );
			$html = wp_remote_retrieve_body( $response );
			if ( 200 === $code && is_string( $html ) && strlen( $html ) > 500 ) {
				break;
			}
			BJ_Logger::warning( 'Scrape HTTP ' . $code . ' attempt ' . $attempts );
			sleep( min( 2 * $attempts, 10 ) );
		}

		if ( 200 !== $code || ! is_string( $html ) || strlen( $html ) < 500 ) {
			return new WP_Error( 'fetch_failed', __( 'Could not load check-in page.', 'beer-journal' ) );
		}

		return $this->parse_html( $html, $url );
	}

	/**
	 * Respect delay between HTTP calls.
	 *
	 * @param int $delay Seconds.
	 * @return void
	 */
	private function respect_rate_limit( $delay ) {
		$key   = 'bj_last_scrape_ts';
		$last  = (int) get_transient( $key );
		$now   = time();
		$delay = max( 1, $delay );
		if ( $last > 0 && ( $now - $last ) < $delay ) {
			sleep( $delay - ( $now - $last ) );
		}
		set_transient( $key, time(), 120 );
	}

	/**
	 * Parse HTML into check-in fields.
	 *
	 * @param string $html Full HTML.
	 * @param string $url Check-in URL.
	 * @return array<string, mixed>|WP_Error
	 */
	private function parse_html( $html, $url ) {
		$data = array(
			'checkin_id'    => bj_parse_checkin_id_from_url( $url ),
			'checkin_url'   => $url,
			'beer_name'     => '',
			'brewery_name'  => '',
			'beer_style'    => '',
			'beer_abv'      => null,
			'beer_ibu'      => null,
			'rating_raw'    => null,
			'serving_type'  => '',
			'comment'       => '',
			'venue_name'    => '',
			'image_url'     => '',
			'toast_count'   => null,
			'comment_count' => null,
		);

		$this->extract_from_json_ld( $html, $data );
		$this->extract_from_meta( $html, $data );

		try {
			$crawler = new Crawler( $html );
			$this->extract_from_dom( $crawler, $data );
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			BJ_Logger::debug( 'DomCrawler: ' . $e->getMessage() );
		}

		$this->extract_from_regex( $html, $data );

		if ( '' === $data['beer_name'] && '' === $data['brewery_name'] && null === $data['rating_raw'] ) {
			return new WP_Error( 'parse_empty', __( 'Could not parse check-in data from HTML.', 'beer-journal' ) );
		}

		return $data;
	}

	/**
	 * JSON-LD blocks.
	 *
	 * @param string             $html HTML.
	 * @param array<string,mixed> $data Data by ref.
	 * @return void
	 */
	private function extract_from_json_ld( $html, array &$data ) {
		if ( ! preg_match_all( '#<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $blocks ) ) {
			return;
		}
		foreach ( $blocks[1] as $json ) {
			$decoded = json_decode( trim( $json ), true );
			$items   = isset( $decoded['@graph'] ) ? $decoded['@graph'] : array( $decoded );
			foreach ( $items as $node ) {
				if ( ! is_array( $node ) ) {
					continue;
				}
				if ( isset( $node['aggregateRating']['ratingValue'] ) ) {
					$data['rating_raw'] = floatval( $node['aggregateRating']['ratingValue'] );
				}
				if ( isset( $node['name'] ) && is_string( $node['name'] ) && '' === $data['beer_name'] ) {
					$data['beer_name'] = sanitize_text_field( $node['name'] );
				}
			}
		}
	}

	/**
	 * Open Graph and meta tags.
	 *
	 * @param string             $html HTML.
	 * @param array<string,mixed> $data Data.
	 * @return void
	 */
	private function extract_from_meta( $html, array &$data ) {
		if ( preg_match( '/property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m ) ) {
			$data['image_url'] = esc_url_raw( $m[1] );
		}
		if ( preg_match( '/property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m ) ) {
			$title = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			$parsed = bj_parse_rss_item_title( $title );
			if ( '' === $data['beer_name'] && '' !== $parsed['beer'] ) {
				$data['beer_name'] = $parsed['beer'];
			}
			if ( '' === $data['brewery_name'] && '' !== $parsed['brewery'] ) {
				$data['brewery_name'] = $parsed['brewery'];
			}
			if ( '' === $data['venue_name'] && '' !== $parsed['venue'] ) {
				$data['venue_name'] = $parsed['venue'];
			}
		}
	}

	/**
	 * DOM selectors from documentation (best effort).
	 *
	 * @param Crawler            $crawler Crawler.
	 * @param array<string,mixed> $data Data.
	 * @return void
	 */
	private function extract_from_dom( Crawler $crawler, array &$data ) {
		$selectors = BJ_Scraper_Config::dom_selectors();
		$defaults  = array(
			'beer'    => array(),
			'brewery' => array(),
			'style'   => array(),
			'rating'  => array(),
			'comment' => array(),
			'venue'   => array(),
			'photo'   => array(),
		);
		$selectors = wp_parse_args( $selectors, $defaults );

		foreach ( $selectors['beer'] as $sel ) {
			if ( $crawler->filter( $sel )->count() > 0 ) {
				$data['beer_name'] = trim( $crawler->filter( $sel )->first()->text() );
				break;
			}
		}
		foreach ( $selectors['brewery'] as $sel ) {
			if ( $crawler->filter( $sel )->count() > 0 ) {
				$data['brewery_name'] = trim( $crawler->filter( $sel )->first()->text() );
				break;
			}
		}
		foreach ( $selectors['style'] as $sel ) {
			if ( $crawler->filter( $sel )->count() > 0 ) {
				$data['beer_style'] = trim( $crawler->filter( $sel )->first()->text() );
				break;
			}
		}
		foreach ( $selectors['rating'] as $sel ) {
			if ( $crawler->filter( $sel )->count() > 0 ) {
				$text = trim( $crawler->filter( $sel )->first()->text() );
				if ( is_numeric( $text ) ) {
					$data['rating_raw'] = floatval( $text );
					break;
				}
			}
		}
		foreach ( $selectors['comment'] as $sel ) {
			if ( $crawler->filter( $sel )->count() > 0 ) {
				$data['comment'] = trim( $crawler->filter( $sel )->first()->text() );
				break;
			}
		}
		foreach ( $selectors['venue'] as $sel ) {
			if ( $crawler->filter( $sel )->count() > 0 ) {
				$data['venue_name'] = trim( $crawler->filter( $sel )->first()->text() );
				break;
			}
		}
		foreach ( $selectors['photo'] as $sel ) {
			if ( $crawler->filter( $sel )->count() > 0 ) {
				$src = $crawler->filter( $sel )->first()->attr( 'src' );
				if ( is_string( $src ) && '' !== $src ) {
					$data['image_url'] = esc_url_raw( $src );
					break;
				}
			}
		}

		// ABV / IBU from .details or body text.
		if ( $crawler->filter( '.details' )->count() > 0 ) {
			$txt = $crawler->filter( '.details' )->first()->text();
			if ( preg_match( '/ABV[:\s]*([\d.]+)\s*%/i', $txt, $m ) ) {
				$data['beer_abv'] = floatval( $m[1] );
			}
			if ( preg_match( '/IBU[:\s]*(\d+)/i', $txt, $m ) ) {
				$data['beer_ibu'] = absint( $m[1] );
			}
		}
	}

	/**
	 * Broad regex fallbacks on raw HTML.
	 *
	 * @param string             $html HTML.
	 * @param array<string,mixed> $data Data.
	 * @return void
	 */
	private function extract_from_regex( $html, array &$data ) {
		if ( null === $data['rating_raw'] && preg_match( '/"ratingValue"\s*:\s*([\d.]+)/', $html, $m ) ) {
			$data['rating_raw'] = floatval( $m[1] );
		}
		if ( null === $data['toast_count'] && preg_match( '/"toastCount"\s*:\s*(\d+)/i', $html, $m ) ) {
			$data['toast_count'] = absint( $m[1] );
		}
	}
}
