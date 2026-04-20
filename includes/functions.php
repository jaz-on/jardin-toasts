<?php
/**
 * Global helper functions for Beer Journal.
 *
 * @package BeerJournal
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
function bj_parse_checkin_id_from_url( $url ) {
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
function bj_get_default_rating_rules() {
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
function bj_get_default_rating_labels() {
	return array(
		0 => __( 'Undrinkable', 'beer-journal' ),
		1 => __( 'Terrible', 'beer-journal' ),
		2 => __( 'Poor', 'beer-journal' ),
		3 => __( 'Okay', 'beer-journal' ),
		4 => __( 'Good', 'beer-journal' ),
		5 => __( 'Excellent', 'beer-journal' ),
	);
}

/**
 * Default Untappd RSS feed URL used when the option is not stored yet.
 *
 * Optional: define `BJ_RSS_FEED_URL` in wp-config.php to override the default feed URL.
 *
 * @return string
 */
function bj_get_default_rss_feed_url() {
	if ( defined( 'BJ_RSS_FEED_URL' ) && is_string( BJ_RSS_FEED_URL ) && '' !== trim( BJ_RSS_FEED_URL ) ) {
		return apply_filters( 'bj_default_rss_feed_url', esc_url_raw( BJ_RSS_FEED_URL ) );
	}
	return apply_filters(
		'bj_default_rss_feed_url',
		'https://untappd.com/rss/user/jaz_on?key=89731ff4bd5fc508dc3eae87a6cf93f4'
	);
}

/**
 * Effective RSS feed URL: stored value, or default when the option has never been saved.
 *
 * An intentionally empty saved value stays empty (disables sync until configured again).
 *
 * @return string
 */
function bj_get_rss_feed_url() {
	$stored = get_option( 'bj_rss_feed_url', false );
	if ( false === $stored ) {
		return bj_get_default_rss_feed_url();
	}
	return trim( (string) $stored );
}

/**
 * Default Untappd profile username (historical import / examples).
 *
 * @return string
 */
function bj_get_default_untappd_username() {
	return apply_filters( 'bj_default_untappd_username', 'jaz_on' );
}

/**
 * Username field value: stored, or default when the option was never saved.
 *
 * @return string
 */
function bj_get_untappd_username() {
	$stored = get_option( 'bj_untappd_username', false );
	if ( false === $stored ) {
		return bj_get_default_untappd_username();
	}
	return (string) $stored;
}

/**
 * Normalize check-in post content for storage and display (paragraphs for plain text).
 *
 * @param string $content Raw or HTML comment.
 * @return string
 */
function bj_normalize_imported_post_content( $content ) {
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
function bj_map_rating_raw_to_rounded( $raw ) {
	if ( null === $raw || '' === $raw ) {
		return null;
	}
	$raw = floatval( $raw );
	$rules = get_option( 'bj_rating_rules', bj_get_default_rating_rules() );
	$rules = apply_filters( 'bj_rating_rules', $rules );
	if ( ! is_array( $rules ) || empty( $rules ) ) {
		$rules = bj_get_default_rating_rules();
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
function bj_parse_rss_item_title( $title ) {
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
function bj_get_log_directory() {
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		return false;
	}
	$dir = trailingslashit( $upload['basedir'] ) . 'beer-journal/logs/';
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
function bj_get_post_id_by_checkin_id( $checkin_id ) {
	global $wpdb;
	$checkin_id = sanitize_text_field( (string) $checkin_id );
	if ( '' === $checkin_id ) {
		return 0;
	}
	$key = '_bj_checkin_id';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$post_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
			$key,
			$checkin_id
		)
	);
	return $post_id ? absint( $post_id ) : 0;
}

/**
 * Transient-backed cache helper (see docs/development/caching.md).
 *
 * @param string   $key      Short key (prefix bj_ added).
 * @param callable $producer Callback returning data to cache.
 * @param int|null $ttl      TTL seconds; default 1 hour.
 * @return mixed
 */
function bj_get_cached_data( $key, $producer, $ttl = null ) {
	$key       = preg_replace( '/[^a-z0-9_\-]/i', '', (string) $key );
	$cache_key = 'bj_' . $key;
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
function bj_invalidate_stats_cache() {
	delete_transient( 'bj_global_stats' );
}

/**
 * Cached post counts for beer_checkin.
 *
 * @return array{publish: int, draft: int}
 */
function bj_get_global_stats() {
	return bj_get_cached_data(
		'global_stats',
		function () {
			$counts = wp_count_posts( BJ_Post_Type::POST_TYPE );
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
function bj_send_notification_email( $subject, $body, $type = 'error' ) {
	if ( 'sync' === $type && ! get_option( 'bj_notify_on_sync', false ) ) {
		return;
	}
	if ( 'error' === $type && ! get_option( 'bj_notify_on_error', true ) ) {
		return;
	}
	$to = get_option( 'bj_notification_email', '' );
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
function bj_touch_last_rss_sync_time() {
	update_option( 'bj_last_rss_sync_at', gmdate( 'c' ), false );
}

/**
 * Archive layout: grid or table (option bj_archive_layout).
 *
 * @return string grid|table
 */
function bj_get_archive_layout() {
	$l = get_option( 'bj_archive_layout', 'grid' );
	return in_array( $l, array( 'grid', 'table' ), true ) ? $l : 'grid';
}
