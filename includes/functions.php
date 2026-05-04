<?php
/**
 * Global helper functions for Jardin Toasts.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extract Untappd check-in ID from a check-in URL.
 *
 * @param string $url Check-in URL.
 * @return string|null Numeric ID or null.
 */
function jt_parse_checkin_id_from_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return null;
	}
	if ( preg_match( '#/checkin/(\d+)#', $url, $m ) ) {
		return $m[1];
	}
	return null;
}

/**
 * Default rating mapping rules (0–5 raw to 0–5 stars).
 *
 * @return array<int, array{min: float, max: float, round: int}>
 */
function jt_get_default_rating_rules() {
	return array(
		array( 'min' => 0.0, 'max' => 0.49, 'round' => 0 ),
		array( 'min' => 0.5, 'max' => 1.49, 'round' => 1 ),
		array( 'min' => 1.5, 'max' => 2.49, 'round' => 2 ),
		array( 'min' => 2.5, 'max' => 3.49, 'round' => 3 ),
		array( 'min' => 3.5, 'max' => 4.49, 'round' => 4 ),
		array( 'min' => 4.5, 'max' => 5.0, 'round' => 5 ),
	);
}

/**
 * Default labels for rounded ratings (0–5).
 *
 * @return array<int, string>
 */
function jt_get_default_rating_labels() {
	return array(
		0 => __( 'Undrinkable', 'jardin-toasts' ),
		1 => __( 'Terrible', 'jardin-toasts' ),
		2 => __( 'Poor', 'jardin-toasts' ),
		3 => __( 'Okay', 'jardin-toasts' ),
		4 => __( 'Good', 'jardin-toasts' ),
		5 => __( 'Excellent', 'jardin-toasts' ),
	);
}

/**
 * Stored rating labels (0–5) merged with defaults.
 *
 * @return array<int, string>
 */
function jt_get_rating_labels() {
	$defaults = jt_get_default_rating_labels();
	$stored   = get_option( 'jt_rating_labels', false );
	if ( false === $stored || ! is_array( $stored ) ) {
		return apply_filters( 'jardin_toasts_rating_labels', apply_filters( 'jt_rating_labels', $defaults ) );
	}
	$out = array();
	for ( $i = 0; $i <= 5; $i++ ) {
		$out[ $i ] = isset( $stored[ $i ] ) && '' !== $stored[ $i ]
			? (string) $stored[ $i ]
			: ( isset( $defaults[ $i ] ) ? $defaults[ $i ] : '' );
	}
	return apply_filters( 'jardin_toasts_rating_labels', apply_filters( 'jt_rating_labels', $out ) );
}

/**
 * Default Untappd RSS feed URL used when the option is not stored yet.
 *
 * Optional: define `JT_RSS_FEED_URL` in wp-config.php to override the default feed URL.
 *
 * @return string
 */
function jt_get_default_rss_feed_url() {
	if ( defined( 'JT_RSS_FEED_URL' ) && is_string( JT_RSS_FEED_URL ) && '' !== trim( JT_RSS_FEED_URL ) ) {
		return apply_filters( 'jardin_toasts_default_rss_feed_url', apply_filters( 'jt_default_rss_feed_url', esc_url_raw( JT_RSS_FEED_URL ) ) );
	}
	return apply_filters(
		'jardin_toasts_default_rss_feed_url',
		apply_filters(
			'jt_default_rss_feed_url',
			'https://untappd.com/rss/user/jaz_on?key=89731ff4bd5fc508dc3eae87a6cf93f4'
		)
	);
}

/**
 * Effective RSS feed URL: stored value, or default when the option has never been saved.
 *
 * An intentionally empty saved value stays empty (disables sync until configured again).
 *
 * @return string
 */
function jt_get_rss_feed_url() {
	$stored = get_option( 'jt_rss_feed_url', false );
	if ( false === $stored ) {
		return jt_get_default_rss_feed_url();
	}
	return trim( (string) $stored );
}

/**
 * Default Untappd profile username (historical import / examples).
 *
 * @return string
 */
function jt_get_default_untappd_username() {
	return apply_filters( 'jardin_toasts_default_untappd_username', apply_filters( 'jt_default_untappd_username', 'jaz_on' ) );
}

/**
 * Username field value: stored, or default when the option was never saved.
 *
 * @return string
 */
function jt_get_untappd_username() {
	$stored = get_option( 'jt_untappd_username', false );
	if ( false === $stored ) {
		return jt_get_default_untappd_username();
	}
	return (string) $stored;
}

/**
 * Optional Untappd browser session cookie string for authenticated HTML scraping.
 *
 * Paste the raw `Cookie` header value from DevTools (Application → Cookies, or Network
 * request headers) while logged in on untappd.com. Do not commit this value.
 *
 * @return string
 */
function jt_get_untappd_session_cookie() {
	$raw = class_exists( 'JT_Settings' ) ? JT_Settings::get( 'jt_untappd_session_cookie' ) : (string) get_option( 'jt_untappd_session_cookie', '' );
	if ( ! is_string( $raw ) ) {
		return '';
	}
	$raw = trim( $raw );
	if ( '' === $raw ) {
		return '';
	}
	if ( preg_match( '/^cookie:\s*/i', $raw ) ) {
		$raw = trim( (string) preg_replace( '/^cookie:\s*/i', '', $raw ) );
	}
	return $raw;
}

/**
 * HTTP headers for Untappd HTML requests (profile, more_feed, check-in pages).
 *
 * @param string $referer_url Full Referer URL, or empty to omit.
 * @return array<string, string>
 */
function jt_untappd_http_headers( $referer_url = '' ) {
	$headers = array(
		'Accept'          => 'text/html',
		'Accept-Language' => 'en-US,en;q=0.9',
	);
	if ( is_string( $referer_url ) && '' !== $referer_url ) {
		$headers['Referer'] = $referer_url;
	}
	$cookie = jt_get_untappd_session_cookie();
	if ( '' !== $cookie ) {
		$headers['Cookie'] = $cookie;
	}
	return (array) apply_filters(
		'jardin_toasts_untappd_http_headers',
		apply_filters( 'jt_untappd_http_headers', $headers, $referer_url ),
		$referer_url
	);
}

/**
 * User-Agent for Untappd when a session cookie is used (plugin UA is often blocked).
 *
 * @return string
 */
function jt_untappd_outbound_user_agent() {
	$default = jt_http_user_agent_string();
	if ( '' === jt_get_untappd_session_cookie() ) {
		return $default;
	}
	$browser = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:128.0) Gecko/20100101 Firefox/128.0';
	return (string) apply_filters(
		'jardin_toasts_untappd_session_user_agent',
		apply_filters( 'jt_untappd_session_user_agent', $browser, $default ),
		$default
	);
}

/**
 * Whether HTML looks like a Cloudflare challenge or block page.
 *
 * @param string $html Response body.
 * @return bool
 */
function jt_untappd_html_looks_like_cloudflare_challenge( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return false;
	}
	$h = strtolower( $html );
	return str_contains( $h, 'cf-browser-verification' )
		|| str_contains( $h, 'cf-challenge' )
		|| str_contains( $h, 'just a moment' )
		|| str_contains( $h, 'attention required' )
		|| str_contains( $h, 'enable javascript and cookies' );
}

/**
 * GET Untappd with session cookie, without following redirects (more_feed returns 303 to home when rejected).
 *
 * @param string $url     Full URL.
 * @param string $referer Referer header (profile URL).
 * @return string|WP_Error Body on 200, error otherwise.
 */
function jt_untappd_remote_get_with_session( $url, $referer ) {
	$response = wp_remote_get(
		$url,
		array(
			'timeout'     => 25,
			'redirection' => 0,
			'headers'     => jt_untappd_http_headers( $referer ),
			'user-agent'  => jt_untappd_outbound_user_agent(),
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	if ( $code >= 300 && $code < 400 ) {
		$loc = wp_remote_retrieve_header( $response, 'location' );
		$loc = is_string( $loc ) ? $loc : '';
		return new WP_Error(
			'untappd_session_redirect',
			sprintf(
				/* translators: 1: HTTP status code, 2: Location header or "none" */
				__( 'Untappd returned HTTP %1$d (redirect) instead of the activity feed — the session cookie is not accepted for this request. Location: %2$s. Common causes: cookie copied from another machine, expired login, or Cloudflare token (cf_clearance) tied to a different IP than your WordPress server.', 'jardin-toasts' ),
				(int) $code,
				'' !== $loc ? $loc : __( '(none)', 'jardin-toasts' )
			)
		);
	}
	if ( 200 !== (int) $code ) {
		return new WP_Error(
			'untappd_session_http',
			sprintf(
				/* translators: %d: HTTP status */
				__( 'Untappd returned HTTP %d for the authenticated request.', 'jardin-toasts' ),
				(int) $code
			)
		);
	}
	$body = wp_remote_retrieve_body( $response );
	if ( ! is_string( $body ) ) {
		return new WP_Error( 'untappd_session_body', __( 'Empty response body from Untappd.', 'jardin-toasts' ) );
	}
	if ( jt_untappd_html_looks_like_cloudflare_challenge( $body ) ) {
		return new WP_Error(
			'untappd_session_cf',
			__( 'Untappd returned a Cloudflare challenge page instead of your profile. Your host’s outbound IP must pass Cloudflare the same way your browser does; a cookie copied from your laptop often fails on a remote server.', 'jardin-toasts' )
		);
	}
	return $body;
}

/**
 * Queue a short notice appended to the next “Discover check-ins” AJAX success message.
 *
 * @param string $message User-facing text.
 * @return void
 */
function jt_set_discover_session_notice( $message ) {
	$s = is_string( $message ) ? trim( $message ) : '';
	if ( '' === $s ) {
		return;
	}
	$prev = get_transient( 'jt_discover_session_notice' );
	if ( is_string( $prev ) && '' !== $prev ) {
		$s = trim( $prev . ' ' . $s );
	}
	set_transient( 'jt_discover_session_notice', $s, 2 * MINUTE_IN_SECONDS );
}

/**
 * Consume a pending discover notice (single use).
 *
 * @return string
 */
function jt_take_discover_session_notice() {
	$v = get_transient( 'jt_discover_session_notice' );
	delete_transient( 'jt_discover_session_notice' );
	return is_string( $v ) ? $v : '';
}

/**
 * Normalized batch size / crawl delay choices for the settings UI.
 *
 * @return array{batch_current:int,delay_current:int,batch_choices:array<int,string>,delay_choices:array<int,string>}
 */
function jt_settings_importer_choice_lists() {
	$batch_current = (int) JT_Settings::get( 'jt_import_batch_size' );
	$delay_current = (int) JT_Settings::get( 'jt_import_delay' );
	$batch_choices = array(
		10  => __( '10 check-ins — small steps', 'jardin-toasts' ),
		15  => __( '15 check-ins — light', 'jardin-toasts' ),
		25  => __( '25 check-ins — balanced (recommended)', 'jardin-toasts' ),
		40  => __( '40 check-ins — fewer clicks', 'jardin-toasts' ),
		50  => __( '50 check-ins — large (may time out)', 'jardin-toasts' ),
	);
	$delay_choices = array(
		0 => __( 'No pause (fast hosts only)', 'jardin-toasts' ),
		1 => __( '1 second between requests', 'jardin-toasts' ),
		2 => __( '2 seconds — gentle', 'jardin-toasts' ),
		3 => __( '3 seconds — polite (default)', 'jardin-toasts' ),
		5 => __( '5 seconds — very safe', 'jardin-toasts' ),
		8 => __( '8 seconds — slowest', 'jardin-toasts' ),
	);
	if ( ! array_key_exists( $batch_current, $batch_choices ) ) {
		$batch_choices[ $batch_current ] = sprintf(
			/* translators: %d: number of check-ins */
			__( '%d check-ins (current)', 'jardin-toasts' ),
			$batch_current
		);
		ksort( $batch_choices, SORT_NUMERIC );
	}
	if ( ! array_key_exists( $delay_current, $delay_choices ) ) {
		$delay_choices[ $delay_current ] = sprintf(
			/* translators: %d: seconds */
			__( '%d seconds (current)', 'jardin-toasts' ),
			$delay_current
		);
		ksort( $delay_choices, SORT_NUMERIC );
	}
	return compact( 'batch_current', 'delay_current', 'batch_choices', 'delay_choices' );
}

/**
 * Check-in IDs from the configured Untappd RSS feed (for discovery merge).
 *
 * Untappd’s public profile HTML only exposes a few recent check-ins; the RSS feed
 * lists more items (~25) without signing in.
 *
 * @return list<string>
 */
function jt_discovery_feed_checkin_ids() {
	$url = jt_get_rss_feed_url();
	if ( ! is_string( $url ) || '' === trim( $url ) ) {
		return array();
	}
	if ( ! function_exists( 'fetch_feed' ) ) {
		require_once ABSPATH . WPINC . '/feed.php';
	}
	$feed = fetch_feed( $url );
	if ( is_wp_error( $feed ) ) {
		JT_Logger::warning( 'Discovery: RSS feed not readable — ' . $feed->get_error_message() );
		return array();
	}
	$items = $feed->get_items( 0, 50 );
	if ( empty( $items ) ) {
		return array();
	}
	$out = array();
	foreach ( $items as $item ) {
		$link = $item->get_link();
		if ( ! is_string( $link ) || '' === $link ) {
			continue;
		}
		$cid = jt_parse_checkin_id_from_url( $link );
		if ( $cid ) {
			$out[ (string) $cid ] = true;
		}
	}
	return array_keys( $out );
}

/**
 * User-Agent for outbound HTTP (scrape, crawl).
 *
 * Same filter order as other doubles (`jt_*` then `jardin_toasts_*`).
 *
 * @return string
 */
function jt_http_user_agent_string() {
	$default = 'Jardin Toasts/' . JT_VERSION . '; ' . home_url( '/' );
	return (string) apply_filters(
		'jardin_toasts_http_user_agent',
		(string) apply_filters( 'jt_http_user_agent', $default )
	);
}

/**
 * Extract Untappd profile username from an RSS feed URL.
 *
 * @param string $url RSS URL.
 * @return string Username slug or empty.
 */
function jt_parse_username_from_rss_url( $url ) {
	if ( ! is_string( $url ) || '' === trim( $url ) ) {
		return '';
	}
	if ( preg_match( '~untappd\.com/rss/user/([^/?#&]+)~i', $url, $m ) ) {
		return sanitize_user( rawurldecode( $m[1] ), true );
	}
	return '';
}

/**
 * Public URL for the beer check-in archive (rewrite slug: checkins).
 *
 * @return string
 */
function jt_get_checkin_archive_url() {
	$pt = 'beer_checkin';
	if ( ! post_type_exists( $pt ) ) {
		return home_url( '/' );
	}
	$link = get_post_type_archive_link( $pt );
	return is_string( $link ) && '' !== $link ? $link : home_url( '/' );
}

/**
 * Normalize check-in post content for storage and display (paragraphs for plain text).
 *
 * @param string $content Raw or HTML comment.
 * @return string
 */
function jt_normalize_imported_post_content( $content ) {
	$content = trim( (string) $content );
	if ( '' === $content ) {
		return '';
	}
	$content = wp_kses_post( $content );
	// Already has block-level markup from Untappd / scraper.
	if ( preg_match( '/<(p|div|ul|ol|blockquote|h[1-6]|table|pre|figure)\b/i', $content ) ) {
		return $content;
	}
	return wpautop( $content );
}

/**
 * Map raw Untappd rating (0–5) to rounded star level using stored rules.
 *
 * @param float|null $raw Raw rating.
 * @return int|null Rounded 0–5 or null if unknown.
 */
function jt_map_rating_raw_to_rounded( $raw ) {
	if ( null === $raw || '' === $raw ) {
		return null;
	}
	$raw = floatval( $raw );
	$rules = get_option( 'jt_rating_rules', jt_get_default_rating_rules() );
	$rules = apply_filters( 'jardin_toasts_rating_rules', apply_filters( 'jt_rating_rules', $rules ) );
	if ( ! is_array( $rules ) || empty( $rules ) ) {
		$rules = jt_get_default_rating_rules();
	}
	foreach ( $rules as $rule ) {
		if ( ! is_array( $rule ) || ! isset( $rule['min'], $rule['max'], $rule['round'] ) ) {
			continue;
		}
		if ( $raw >= floatval( $rule['min'] ) && $raw <= floatval( $rule['max'] ) ) {
			return absint( $rule['round'] );
		}
	}
	return (int) round( min( 5, max( 0, $raw ) ) );
}

/**
 * Parse Untappd RSS item title for beer, brewery, venue (best effort).
 *
 * @param string $title Item title.
 * @return array{beer: string, brewery: string, venue: string}
 */
function jt_parse_rss_item_title( $title ) {
	$out = array(
		'beer'    => '',
		'brewery' => '',
		'venue'   => '',
	);
	if ( ! is_string( $title ) || '' === $title ) {
		return $out;
	}
	// "User is drinking a Beer Name by Brewery Name at Venue Name"
	if ( preg_match( '/is drinking (?:an? )?(.+?) by (.+?) at (.+)$/iu', $title, $m ) ) {
		$out['beer']    = trim( html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		$out['brewery'] = trim( html_entity_decode( $m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		$out['venue']   = trim( html_entity_decode( $m[3], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		return $out;
	}
	return $out;
}

/**
 * Ensure uploads log directory exists.
 *
 * @return string|false Absolute path or false.
 */
function jt_get_log_directory() {
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		return false;
	}
	$dir = trailingslashit( $upload['basedir'] ) . 'jardin-toasts/logs/';
	if ( ! wp_mkdir_p( $dir ) ) {
		return false;
	}
	$idx = $dir . 'index.php';
	if ( ! file_exists( $idx ) ) {
		file_put_contents( $idx, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
	return $dir;
}

/**
 * Find post ID by Untappd check-in ID.
 *
 * @param string $checkin_id Untappd check-in ID.
 * @return int Post ID or 0.
 */
function jt_get_post_id_by_checkin_id( $checkin_id ) {
	global $wpdb;
	$checkin_id = sanitize_text_field( (string) $checkin_id );
	if ( '' === $checkin_id ) {
		return 0;
	}
	foreach ( array( '_jt_checkin_id', '_jb_checkin_id' ) as $key ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
				$key,
				$checkin_id
			)
		);
		if ( $post_id ) {
			return absint( $post_id );
		}
	}
	return 0;
}

/**
 * Map Untappd check-in IDs to post IDs (batch, one query).
 *
 * @param array<int, string|int> $checkin_ids Check-in IDs.
 * @return array<string, int> checkin_id => post_id
 */
function jt_get_post_ids_by_checkin_ids( array $checkin_ids ) {
	global $wpdb;
	$clean = array();
	foreach ( $checkin_ids as $id ) {
		$s = sanitize_text_field( (string) $id );
		if ( '' !== $s ) {
			$clean[ $s ] = true;
		}
	}
	$ids = array_keys( $clean );
	if ( empty( $ids ) ) {
		return array();
	}
	$ids = array_slice( $ids, 0, 100 );
	$placeholders = implode( ',', array_fill( 0, count( $ids ), '%s' ) );
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPlaceholder
	$sql = "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_jt_checkin_id','_jb_checkin_id') AND meta_value IN ({$placeholders})";
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$prepared = $wpdb->prepare( $sql, ...$ids );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows = $wpdb->get_results( $prepared, ARRAY_A );
	if ( ! is_array( $rows ) ) {
		return array();
	}
	$map = array();
	foreach ( $rows as $row ) {
		if ( isset( $row['meta_value'], $row['post_id'] ) ) {
			$map[ (string) $row['meta_value'] ] = absint( $row['post_id'] );
		}
	}
	return $map;
}

/**
 * Max RSS import attempts per cron run (scrapes + posts). Manual sync uses a higher cap via filter.
 *
 * @param bool $manual True when triggered from admin AJAX.
 * @return int
 */
function jt_get_rss_sync_max_per_run( $manual = false ) {
	if ( $manual ) {
		$max = (int) apply_filters( 'jardin_toasts_rss_manual_sync_max_items', apply_filters( 'jt_rss_manual_sync_max_items', 500 ) );
		return max( 1, $max );
	}
	$n = class_exists( 'JT_Settings' ) ? (int) JT_Settings::get( 'jt_rss_max_per_run' ) : absint( get_option( 'jt_rss_max_per_run', 10 ) );
	$n = max( 1, min( 100, $n ) );
	return (int) apply_filters( 'jardin_toasts_rss_max_per_run', apply_filters( 'jt_rss_max_per_run', $n ) );
}

/**
 * Pause (seconds) between Untappd HTTP requests during HTML scraping (RSS sync paths and check-in scrapes).
 * Minimum 1. Distinct from Historical import → “Pause between requests”, which only applies to admin-driven batches.
 *
 * @return int
 */
function jt_get_scraping_delay_seconds() {
	$d = class_exists( 'JT_Settings' ) ? (int) JT_Settings::get( 'jt_scraping_delay' ) : absint( get_option( 'jt_scraping_delay', 3 ) );
	$d = max( 1, $d );
	return (int) apply_filters( 'jardin_toasts_scraping_delay_seconds', apply_filters( 'jt_scraping_delay_seconds', $d ) );
}

/**
 * Load persisted RSS sync queue rows.
 *
 * @return array<int, array<string, mixed>>
 */
function jt_get_rss_sync_queue() {
	$q = get_option( 'jt_rss_sync_queue', array() );
	return is_array( $q ) ? array_values( $q ) : array();
}

/**
 * Persist RSS sync queue.
 *
 * @param array<int, array<string, mixed>> $queue Rows (FIFO).
 * @return void
 */
function jt_save_rss_sync_queue( array $queue ) {
	update_option( 'jt_rss_sync_queue', array_values( $queue ), false );
}

/**
 * Append rows to the RSS queue without duplicate check-in IDs (later rows win).
 *
 * @param array<int, array<string, mixed>> $queue Existing queue.
 * @param array<int, array<string, mixed>> $rows  Rows to append.
 * @return array<int, array<string, mixed>>
 */
function jt_rss_queue_merge_unique( array $queue, array $rows ) {
	$seen = array();
	$out  = array();
	foreach ( $queue as $row ) {
		if ( ! is_array( $row ) || empty( $row['checkin_id'] ) ) {
			continue;
		}
		$cid = (string) $row['checkin_id'];
		$seen[ $cid ] = count( $out );
		$out[]        = $row;
	}
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || empty( $row['checkin_id'] ) ) {
			continue;
		}
		$cid = (string) $row['checkin_id'];
		if ( isset( $seen[ $cid ] ) ) {
			$out[ $seen[ $cid ] ] = $row;
		} else {
			$seen[ $cid ] = count( $out );
			$out[]        = $row;
		}
	}
	return array_values( $out );
}

/**
 * Schedule a single queue drain if backlog exists and none is pending.
 *
 * @return void
 */
function jt_maybe_schedule_rss_queue_tick() {
	$q = jt_get_rss_sync_queue();
	if ( empty( $q ) ) {
		return;
	}
	$delay = max( 60, jt_get_scraping_delay_seconds() );
	$group = jt_action_scheduler_group();

	if ( jt_using_action_scheduler() ) {
		jt_when_action_scheduler_store_ready(
			static function () use ( $delay, $group ) {
				if ( as_next_scheduled_action( Jardin_Toasts_Keys::HOOK_RSS_QUEUE_TICK, array(), $group ) ) {
					return;
				}
				as_schedule_single_action( time() + $delay, Jardin_Toasts_Keys::HOOK_RSS_QUEUE_TICK, array(), $group );
			}
		);
		return;
	}

	if ( wp_next_scheduled( Jardin_Toasts_Keys::HOOK_RSS_QUEUE_TICK ) ) {
		return;
	}
	wp_schedule_single_event( time() + $delay, Jardin_Toasts_Keys::HOOK_RSS_QUEUE_TICK );
}

/**
 * (Re)schedule a single background historical-import batch at a concrete Unix time.
 *
 * @param int $run_at Unix timestamp when {@see Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH} should run.
 * @return void
 */
function jt_schedule_background_import_batch_at( $run_at ) {
	$run_at = absint( $run_at );
	$group  = jt_action_scheduler_group();

	if ( jt_using_action_scheduler() ) {
		jt_when_action_scheduler_store_ready(
			static function () use ( $run_at, $group ) {
				if ( function_exists( 'as_unschedule_all_actions' ) ) {
					as_unschedule_all_actions( Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH, array(), $group );
				}
				as_schedule_single_action( $run_at, Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH, array(), $group );
			}
		);
		return;
	}

	wp_clear_scheduled_hook( Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH );
	wp_schedule_single_event( $run_at, Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH );
}

/**
 * Queue a background import batch if the historical import checkpoint still has check-ins.
 *
 * @param int|null $delay_seconds Seconds from now until the run; null uses the default polite spacing between batches.
 * @return void
 */
function jt_maybe_schedule_background_import_batch( $delay_seconds = null ) {
	$cp = get_option( 'jt_import_checkpoint', array() );
	if ( ! is_array( $cp ) || empty( $cp['queue'] ) || ! is_array( $cp['queue'] ) ) {
		return;
	}
	$offset = null === $delay_seconds
		? max( 60, absint( get_option( 'jt_import_delay', 3 ) ) * 10 )
		: max( 5, absint( $delay_seconds ) );
	jt_schedule_background_import_batch_at( time() + $offset );
}

/**
 * Run a callback once Action Scheduler has initialized its data store (AS 3.1.6+).
 * Calling as_* APIs earlier triggers _doing_it_wrong notices.
 *
 * @param callable():void $callback Callback using Action Scheduler APIs.
 * @return void
 */
function jt_when_action_scheduler_store_ready( callable $callback ) {
	if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
		return;
	}
	if ( did_action( 'action_scheduler_init' ) ) {
		$callback();
		return;
	}
	add_action( 'action_scheduler_init', $callback, 10, 0 );
}

/**
 * Whether the Action Scheduler API is available (WooCommerce or standalone plugin).
 *
 * @return bool
 */
function jt_using_action_scheduler() {
	return function_exists( 'as_schedule_recurring_action' )
		&& function_exists( 'as_next_scheduled_action' )
		&& function_exists( 'as_schedule_single_action' );
}

/**
 * Action Scheduler group for all Jardin Toasts jobs.
 *
 * @return string
 */
function jt_action_scheduler_group() {
	return 'jardin-toasts';
}

/**
 * Transient-backed cache helper (see docs/development/caching.md).
 *
 * @param string   $key      Short key (prefix jt_ added).
 * @param callable $producer Callback returning data to cache.
 * @param int|null $ttl      TTL seconds; default 1 hour.
 * @return mixed
 */
function jt_get_cached_data( $key, $producer, $ttl = null ) {
	$key       = preg_replace( '/[^a-z0-9_\-]/i', '', (string) $key );
	$cache_key = 'jt_' . $key;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}
	$data = call_user_func( $producer );
	$ttl  = null !== $ttl ? absint( $ttl ) : HOUR_IN_SECONDS;
	set_transient( $cache_key, $data, max( 60, $ttl ) );
	return $data;
}

/**
 * Invalidate global stats transient after imports.
 *
 * @return void
 */
function jt_invalidate_stats_cache() {
	delete_transient( 'jt_global_stats' );
	delete_transient( 'jt_incomplete_checkin_count' );
}

/**
 * Approximate count of draft check-ins missing scraped data (cached briefly).
 *
 * @return int
 */
function jt_count_draft_incomplete_checkins() {
	if ( ! post_type_exists( JT_Post_Type::POST_TYPE ) ) {
		return 0;
	}
	return (int) jt_get_cached_data(
		'incomplete_checkin_count',
		static function () {
			$q = new WP_Query(
				array(
					'post_type'              => JT_Post_Type::POST_TYPE,
					'post_status'            => 'draft',
					'fields'                 => 'ids',
					'posts_per_page'         => 200,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'     => '_jt_incomplete_reason',
							'compare' => 'EXISTS',
						),
					),
				)
			);
			return is_array( $q->posts ) ? count( $q->posts ) : 0;
		},
		15 * MINUTE_IN_SECONDS
	);
}

/**
 * Re-fetch Untappd HTML for an existing check-in post and update meta/content.
 *
 * @param int $post_id Post ID.
 * @return int|WP_Error Post ID on success.
 */
function jt_rescrape_checkin_post( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error( 'forbidden', __( 'You cannot edit this check-in.', 'jardin-toasts' ) );
	}
	if ( get_post_type( $post_id ) !== JT_Post_Type::POST_TYPE ) {
		return new WP_Error( 'wrong_type', __( 'Not a beer check-in post.', 'jardin-toasts' ) );
	}
	$url = get_post_meta( $post_id, '_jt_checkin_url', true );
	if ( ! is_string( $url ) || '' === $url || false === strpos( $url, 'untappd.com' ) ) {
		return new WP_Error( 'no_url', __( 'No valid Untappd check-in URL on this post.', 'jardin-toasts' ) );
	}
	$cid = get_post_meta( $post_id, '_jt_checkin_id', true );
	$data = array(
		'checkin_id'   => is_string( $cid ) && '' !== $cid ? $cid : (string) jt_parse_checkin_id_from_url( $url ),
		'checkin_url'  => esc_url_raw( $url ),
		'checkin_date' => (string) get_post_meta( $post_id, '_jt_checkin_date', true ),
	);
	if ( '' === $data['checkin_id'] ) {
		return new WP_Error( 'no_id', __( 'Missing check-in ID.', 'jardin-toasts' ) );
	}
	$scraper = new JT_Scraper();
	$scraped = $scraper->scrape_checkin_url( $url );
	if ( ! is_wp_error( $scraped ) ) {
		$data = array_merge( $data, $scraped );
	} else {
		JT_Logger::warning( 'Re-scrape failed for post ' . $post_id . ': ' . $scraped->get_error_message() );
		return $scraped;
	}
	$importer = new JT_Importer();
	return $importer->import_checkin_data( $data, 'rss' );
}

/**
 * Cached post counts for beer_checkin.
 *
 * @return array{publish: int, draft: int}
 */
function jt_get_global_stats() {
	return jt_get_cached_data(
		'global_stats',
		function () {
			$counts = wp_count_posts( JT_Post_Type::POST_TYPE );
			return array(
				'publish' => isset( $counts->publish ) ? (int) $counts->publish : 0,
				'draft'   => isset( $counts->draft ) ? (int) $counts->draft : 0,
			);
		},
		HOUR_IN_SECONDS
	);
}

/**
 * Send admin notification email (sync / errors).
 *
 * @param string $subject Email subject.
 * @param string $body    Plain body.
 * @param string $type    sync|error.
 * @return void
 */
function jt_send_notification_email( $subject, $body, $type = 'error' ) {
	if ( 'sync' === $type && ! get_option( 'jt_notify_on_sync', false ) ) {
		return;
	}
	if ( 'error' === $type && ! get_option( 'jt_notify_on_error', true ) ) {
		return;
	}
	$to = get_option( 'jt_notification_email', '' );
	if ( ! is_string( $to ) || '' === trim( $to ) ) {
		$to = (string) get_option( 'admin_email', '' );
	}
	$to = sanitize_email( $to );
	if ( ! is_email( $to ) ) {
		return;
	}
	wp_mail( $to, wp_strip_all_tags( $subject ), wp_strip_all_tags( $body ) );
}

/**
 * Record last successful RSS sync attempt time (ISO 8601).
 *
 * @return void
 */
function jt_touch_last_rss_sync_time() {
	update_option( 'jt_last_rss_sync_at', gmdate( 'c' ), false );
}

/**
 * Archive layout: grid or table (option jt_archive_layout).
 *
 * @return string grid|table
 */
function jt_get_archive_layout() {
	$l = get_option( 'jt_archive_layout', 'grid' );
	return in_array( $l, array( 'grid', 'table' ), true ) ? $l : 'grid';
}

/**
 * Canonical public identifiers (cron, AJAX, nonces) and legacy `jt_*` cron cleanup.
 */
final class Jardin_Toasts_Keys {

	public const OPTION_CRON_HOOKS_MIGRATED = 'jardin_toasts_cron_hooks_migrated_v1';

	public const NONCE_ADMIN_AJAX = 'jardin_toasts_admin';

	public const HOOK_RSS_SYNC = 'jardin_toasts_rss_sync';
	public const HOOK_RSS_QUEUE_TICK = 'jardin_toasts_rss_queue_tick';
	public const HOOK_BACKGROUND_IMPORT_BATCH = 'jardin_toasts_background_import_batch';
	public const HOOK_DAILY_LOG_CLEANUP = 'jardin_toasts_daily_log_cleanup';

	public const AJAX_SYNC_NOW = 'jardin_toasts_sync_now';
	public const AJAX_CRAWL_DISCOVER = 'jardin_toasts_crawl_discover';
	public const AJAX_CRAWL_BATCH = 'jardin_toasts_crawl_batch';
	public const AJAX_TEST_RSS = 'jardin_toasts_test_rss';
	public const AJAX_TEST_PROFILE = 'jardin_toasts_test_profile';

	public const BULK_RESCRAPE = 'jardin_toasts_bulk_rescrape';

	/**
	 * @return list<string>
	 */
	public static function legacy_jt_cron_hooks(): array {
		return array(
			'jt_rss_sync',
			'jt_rss_queue_tick',
			'jt_background_import_batch',
			'jt_daily_log_cleanup',
		);
	}

	/**
	 * @return list<string>
	 */
	public static function canonical_cron_hooks(): array {
		return array(
			self::HOOK_RSS_SYNC,
			self::HOOK_RSS_QUEUE_TICK,
			self::HOOK_BACKGROUND_IMPORT_BATCH,
			self::HOOK_DAILY_LOG_CLEANUP,
		);
	}

	/**
	 * @return list<string>
	 */
	public static function legacy_jt_and_jb_rss_hooks(): array {
		return array_values(
			array_unique(
				array_merge(
					self::legacy_jt_cron_hooks(),
					array(
						'jb_rss_sync',
						'jb_rss_queue_tick',
						'jb_background_import_batch',
						'jb_daily_log_cleanup',
					)
				)
			)
		);
	}

	/**
	 * @return list<string>
	 */
	public static function all_teardown_cron_hooks(): array {
		return array_values(
			array_unique(
				array_merge(
					self::canonical_cron_hooks(),
					self::legacy_jt_cron_hooks(),
					array(
						'jb_rss_sync',
						'jb_rss_queue_tick',
						'jb_background_import_batch',
						'jb_daily_log_cleanup',
					),
					array(
						'bj_rss_sync',
						'bj_rss_queue_tick',
						'bj_background_import_batch',
						'bj_daily_log_cleanup',
					)
				)
			)
		);
	}

	/**
	 * @return void
	 */
	public static function maybe_migrate_cron_hook_names(): void {
		if ( get_option( self::OPTION_CRON_HOOKS_MIGRATED, '' ) === '1' ) {
			return;
		}

		foreach ( self::legacy_jt_cron_hooks() as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}

		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			update_option( self::OPTION_CRON_HOOKS_MIGRATED, '1', false );
			return;
		}

		$groups = array( 'jardin-toasts', 'beer-journal', 'jardin-beer' );
		$legacy = self::legacy_jt_cron_hooks();

		$finish = static function () use ( $legacy, $groups ): void {
			foreach ( $groups as $group ) {
				foreach ( $legacy as $hook ) {
					as_unschedule_all_actions( $hook, array(), $group );
				}
			}
			update_option( Jardin_Toasts_Keys::OPTION_CRON_HOOKS_MIGRATED, '1', false );
		};

		if ( function_exists( 'jt_using_action_scheduler' ) && jt_using_action_scheduler() && function_exists( 'jt_when_action_scheduler_store_ready' ) ) {
			jt_when_action_scheduler_store_ready( $finish );
			return;
		}

		$finish();
	}
}
