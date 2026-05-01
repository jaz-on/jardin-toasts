<?php
/**
 * Historical import: discover check-in URLs from an Untappd profile.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JT_Crawler
 */
class JT_Crawler {

	/**
	 * Discover check-in IDs from profile pages (up to max_pages).
	 *
	 * @param string $username Untappd username.
	 * @param int    $max_pages Max pages to crawl.
	 * @return array<int,string>|WP_Error Check-in IDs newest-first-ish.
	 */
	public function discover_checkins( $username, $max_pages = 10 ) {
		$username = sanitize_user( $username, true );
		if ( ! $username ) {
			return new WP_Error( 'no_user', __( 'Invalid Untappd username.', 'jardin-toasts' ) );
		}

		$seen  = array();
		$delay = absint( get_option( 'jt_import_delay', 3 ) );
		$max_pages = max( 1, min( 50, $max_pages ) );

		for ( $page = 1; $page <= $max_pages; $page++ ) {
			if ( $page > 1 ) {
				$this->sleep_delay( $delay );
			}

			$url = $this->profile_page_url( $username, $page );
			$url = apply_filters(
				'jardin_toasts_crawler_profile_url',
				apply_filters( 'jt_crawler_profile_url', $url, $username, $page ),
				$username,
				$page
			);

			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 25,
					'headers' => array(
						'Accept' => 'text/html',
						'Accept-Language' => 'en-US,en;q=0.9',
					),
					'user-agent' => apply_filters(
						'jt_http_user_agent',
						'Jardin Toasts/' . JT_VERSION . '; ' . home_url( '/' )
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$html = wp_remote_retrieve_body( $response );
			$code = wp_remote_retrieve_response_code( $response );
			$len  = is_string( $html ) ? strlen( $html ) : 0;

			if ( ! is_string( $html ) || $len < 200 ) {
				if ( 1 === $page && empty( $seen ) ) {
					return new WP_Error(
						'untappd_empty_response',
						sprintf(
							/* translators: 1: HTTP status code, 2: response size in bytes */
							__( 'Untappd returned almost no HTML (HTTP %1$d, %2$d bytes). The server may block outbound requests from your host, or Untappd may require a browser session.', 'jardin-toasts' ),
							(int) $code,
							$len
						)
					);
				}
				break;
			}

			$ids = $this->extract_checkin_ids_from_html( $html, $username );
			if ( empty( $ids ) ) {
				if ( 1 === $page && empty( $seen ) ) {
					$suspicious = ( $len < 4000 || $code < 200 || $code >= 400 );
					if ( $suspicious ) {
						return new WP_Error(
							'untappd_discovery_blocked',
							sprintf(
								/* translators: 1: HTTP status code, 2: response size in bytes */
								__( 'No check-in links found on the first profile page (HTTP %1$d, %2$d bytes). Often this means your web host cannot load Untappd the same way a browser can (bot protection).', 'jardin-toasts' ),
								(int) $code,
								$len
							)
						);
					}
					return new WP_Error(
						'untappd_discovery_markup',
						__( 'No check-in links matched in the profile HTML. Untappd may have changed their page layout; check for a plugin update or report this to the maintainer.', 'jardin-toasts' )
					);
				}
				break;
			}

			$new = false;
			foreach ( $ids as $id ) {
				if ( ! isset( $seen[ $id ] ) ) {
					$seen[ $id ] = true;
					$new = true;
				}
			}

			// If page returns same-only content, stop.
			if ( ! $new && $page > 1 ) {
				break;
			}
		}

		return array_keys( $seen );
	}

	/**
	 * Build profile URL for page index.
	 *
	 * @param string $username Username.
	 * @param int    $page Page number (1-based).
	 * @return string
	 */
	private function profile_page_url( $username, $page ) {
		$base = 'https://untappd.com/user/' . rawurlencode( $username );
		if ( $page <= 1 ) {
			return $base;
		}
		return $base . '?page=' . (int) $page;
	}

	/**
	 * Extract check-in IDs from HTML anchors.
	 *
	 * @param string $html HTML.
	 * @param string $username Expected profile owner (restricts matches to that user path).
	 * @return array<int,string>
	 */
	private function extract_checkin_ids_from_html( $html, $username ) {
		$ids = array();
		$user  = sanitize_user( $username, true );
		$found = array();

		if ( $user && preg_match_all( '#\/user\/' . preg_quote( $user, '#' ) . '\/checkin\/(\d+)#i', $html, $m ) ) {
			foreach ( $m[1] as $id ) {
				$found[] = (string) $id;
			}
		}

		if ( empty( $found ) && preg_match_all( '#\/user\/[^/]+\/checkin\/(\d+)#', $html, $m ) ) {
			foreach ( $m[1] as $id ) {
				$found[] = (string) $id;
			}
		}

		foreach ( $found as $id ) {
			$ids[ $id ] = true;
		}

		return array_keys( $ids );
	}

	/**
	 * Delay between HTTP requests.
	 *
	 * @param int $seconds Seconds.
	 * @return void
	 */
	private function sleep_delay( $seconds ) {
		$seconds = max( 0, $seconds );
		if ( $seconds > 0 ) {
			sleep( $seconds );
		}
	}

	/**
	 * Process one batch from checkpoint queue (background mode).
	 *
	 * @return void
	 */
	public function process_next_batch() {
		$cp = get_option( 'jt_import_checkpoint', array() );
		if ( ! is_array( $cp ) || empty( $cp['queue'] ) || ! is_array( $cp['queue'] ) ) {
			return;
		}

		$batch_size = absint( get_option( 'jt_import_batch_size', 25 ) );
		$batch_size = max( 1, min( 100, $batch_size ) );
		$queue      = $cp['queue'];
		$chunk      = array_splice( $queue, 0, $batch_size );

		$importer = new JT_Importer();
		$username = isset( $cp['username'] ) ? (string) $cp['username'] : '';

		foreach ( $chunk as $checkin_id ) {
			if ( jt_get_post_id_by_checkin_id( (string) $checkin_id ) ) {
				continue;
			}
			$url  = 'https://untappd.com/user/' . rawurlencode( $username ) . '/checkin/' . $checkin_id;
			$data = array(
				'checkin_id'   => (string) $checkin_id,
				'checkin_url'  => $url,
				'checkin_date' => gmdate( 'c' ),
			);

			$scraper = new JT_Scraper();
			$scraped = $scraper->scrape_checkin_url( $url );
			if ( ! is_wp_error( $scraped ) ) {
				$data = array_merge( $data, $scraped );
			}

			$data['source'] = 'crawler';
			$res              = $importer->import_checkin_data( $data, 'crawler' );
			if ( is_wp_error( $res ) ) {
				JT_Logger::warning( 'Historical import: ' . $res->get_error_message() );
			}
		}

		$cp['queue']           = $queue;
		$cp['last_run']        = time();
		$cp['total_imported']  = isset( $cp['total_imported'] ) ? absint( $cp['total_imported'] ) + count( $chunk ) : count( $chunk );
		$cp['status']          = empty( $queue ) ? 'done' : 'running';
		update_option( 'jt_import_checkpoint', $cp, false );

		if ( ! empty( $queue ) && 'background' === get_option( 'jt_import_mode', 'manual' ) ) {
			$delay = time() + max( 60, absint( get_option( 'jt_import_delay', 3 ) ) * 10 );
			if ( function_exists( 'as_schedule_single_action' ) ) {
				jt_when_action_scheduler_store_ready(
					static function () use ( $delay ) {
						as_schedule_single_action( $delay, Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH, array(), jt_action_scheduler_group() );
					}
				);
			} else {
				wp_schedule_single_event( $delay, Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH );
			}
		}
	}
}
