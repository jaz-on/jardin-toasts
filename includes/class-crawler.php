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

		if ( '' !== jt_get_untappd_session_cookie() ) {
			return $this->discover_checkins_with_session( $username, $max_pages );
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
					'timeout'    => 25,
					'headers'    => jt_untappd_http_headers( '' ),
					'user-agent' => jt_http_user_agent_string(),
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
	 * Discover check-in IDs using a browser session cookie (profile + more_feed AJAX HTML).
	 *
	 * Mirrors the logged-in “Show more” flow: GET /user/{user}, then
	 * GET /profile/more_feed/{user}/{offset}?v2=true with decreasing offsets.
	 *
	 * @param string $username Untappd username.
	 * @param int    $max_pages Controls crawl depth (also scales max more_feed rounds via filter).
	 * @return array<int,string>|WP_Error
	 */
	private function discover_checkins_with_session( $username, $max_pages ) {
		$seen      = array();
		$delay     = absint( get_option( 'jt_import_delay', 3 ) );
		$max_pages = max( 1, min( 50, $max_pages ) );
		$profile   = 'https://untappd.com/user/' . rawurlencode( $username );
		$profile   = apply_filters(
			'jardin_toasts_crawler_profile_url',
			apply_filters( 'jt_crawler_profile_url', $profile, $username, 1 ),
			$username,
			1
		);

		$html = jt_untappd_remote_get_with_session( $profile, $profile );
		if ( is_wp_error( $html ) ) {
			return $html;
		}

		$len = strlen( $html );
		if ( $len < 200 ) {
			return new WP_Error(
				'untappd_session_empty',
				sprintf(
					/* translators: %d: response bytes */
					__( 'Untappd profile response was too small with your saved session cookie (%d bytes). The cookie may be expired or incomplete.', 'jardin-toasts' ),
					$len
				)
			);
		}

		foreach ( $this->extract_checkin_ids_from_html( $html, $username ) as $id ) {
			$seen[ $id ] = true;
		}

		$stream_chunk = $this->html_slice_main_stream( $html );
		$offset       = $this->last_checkin_id_in_html_chunk( $stream_chunk );
		if ( null === $offset ) {
			if ( empty( $seen ) ) {
				return new WP_Error(
					'untappd_session_no_stream',
					__( 'Could not find the activity stream on the profile page with your session cookie. Untappd markup may have changed.', 'jardin-toasts' )
				);
			}
			return array_keys( $seen );
		}

		$max_more = (int) apply_filters(
			'jardin_toasts_crawler_session_max_more_feed',
			apply_filters( 'jt_crawler_session_max_more_feed', min( 400, max( 20, $max_pages * 25 ) ), $max_pages, $username ),
			$max_pages,
			$username
		);
		$max_more = max( 1, min( 500, $max_more ) );

		$prev_offset = null;
		for ( $round = 0; $round < $max_more; $round++ ) {
			if ( $offset === $prev_offset ) {
				break;
			}
			$prev_offset = $offset;

			$this->sleep_delay( $delay );

			$feed_url = 'https://untappd.com/profile/more_feed/' . rawurlencode( $username ) . '/' . rawurlencode( (string) $offset ) . '?v2=true';
			$feed_url  = apply_filters(
				'jardin_toasts_crawler_more_feed_url',
				apply_filters( 'jt_crawler_more_feed_url', $feed_url, $username, $offset ),
				$username,
				$offset
			);

			$fragment = jt_untappd_remote_get_with_session( $feed_url, $profile );
			if ( is_wp_error( $fragment ) ) {
				return $fragment;
			}

			$fragment = trim( $fragment );
			if ( '' === $fragment || strlen( $fragment ) < 30 ) {
				break;
			}

			if ( preg_match( '/<!DOCTYPE\s+html/i', $fragment ) && strlen( $fragment ) > 3000 ) {
				return new WP_Error(
					'untappd_session_more_feed_html',
					__( 'Untappd “load more” returned a full web page instead of check-in rows — the session is not valid for pagination from this server (see redirect / Cloudflare notes above).', 'jardin-toasts' )
				);
			}

			$new_ids = $this->extract_checkin_ids_from_html( $fragment, $username );
			if ( empty( $new_ids ) ) {
				break;
			}

			$added = false;
			foreach ( $new_ids as $id ) {
				if ( ! isset( $seen[ $id ] ) ) {
					$seen[ $id ] = true;
					$added       = true;
				}
			}

			$next = $this->last_checkin_id_in_html_chunk( $fragment );
			if ( null === $next || $next === $offset ) {
				break;
			}
			$offset = $next;

			if ( ! $added ) {
				break;
			}
		}

		return array_keys( $seen );
	}

	/**
	 * Narrow HTML to the profile activity stream region (avoids unrelated data-checkin-id nodes).
	 *
	 * @param string $html Full profile document.
	 * @return string|null
	 */
	private function html_slice_main_stream( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return null;
		}
		$start = stripos( $html, 'id="main-stream"' );
		if ( false === $start ) {
			$start = stripos( $html, "id='main-stream'" );
		}
		if ( false === $start ) {
			return null;
		}
		$needles = array( 'more_checkins_logged', 'class="yellow button more_checkins', 'class=\'yellow button more_checkins' );
		$end     = false;
		foreach ( $needles as $needle ) {
			$pos = stripos( $html, $needle, $start + 30 );
			if ( false !== $pos ) {
				$end = $pos;
				break;
			}
		}
		if ( false === $end ) {
			return substr( $html, $start );
		}
		return substr( $html, $start, $end - $start );
	}

	/**
	 * Last data-checkin-id in document order within a chunk (oldest check-in in that chunk for Untappd pagination).
	 *
	 * @param string|null $chunk HTML.
	 * @return string|null
	 */
	private function last_checkin_id_in_html_chunk( $chunk ) {
		if ( ! is_string( $chunk ) || '' === $chunk ) {
			return null;
		}
		if ( ! preg_match_all( '/data-checkin-id\s*=\s*["\'](\d+)["\']/i', $chunk, $m ) || empty( $m[1] ) ) {
			return null;
		}
		$ids = $m[1];
		$last = (string) end( $ids );
		return '' !== $last ? $last : null;
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

		if ( ! empty( $queue ) ) {
			jt_maybe_schedule_background_import_batch();
		}
	}
}
