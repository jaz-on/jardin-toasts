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
 * Stored rating labels (0–5) merged with defaults.
 *
 * @return array<int, string>
 */
function bj_get_rating_labels() {
	$defaults = bj_get_default_rating_labels();
	$stored   = get_option( 'bj_rating_labels', false );
	if ( false === $stored || ! is_array( $stored ) ) {
		return apply_filters( 'bj_rating_labels', $defaults );
	}
	$out = array();
	for ( $i = 0; $i <= 5; $i++ ) {
		$out[ $i ] = isset( $stored[ $i ] ) && '' !== $stored[ $i ]
			? (string) $stored[ $i ]
			: ( isset( $defaults[ $i ] ) ? $defaults[ $i ] : '' );
	}
	return apply_filters( 'bj_rating_labels', $out );
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
 * Extract Untappd profile username from an RSS feed URL.
 *
 * @param string $url RSS URL.
 * @return string Username slug or empty.
 */
function bj_parse_username_from_rss_url( $url ) {
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
function bj_get_checkin_archive_url() {
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
 * Map Untappd check-in IDs to post IDs (batch, one query).
 *
 * @param array<int, string|int> $checkin_ids Check-in IDs.
 * @return array<string, int> checkin_id => post_id
 */
function bj_get_post_ids_by_checkin_ids( array $checkin_ids ) {
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
	$sql = "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value IN ({$placeholders})";
	$params = array_merge( array( '_bj_checkin_id' ), $ids );
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$prepared = $wpdb->prepare( $sql, $params );
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
function bj_get_rss_sync_max_per_run( $manual = false ) {
	if ( $manual ) {
		$max = (int) apply_filters( 'bj_rss_manual_sync_max_items', 500 );
		return max( 1, $max );
	}
	$n = absint( get_option( 'bj_rss_max_per_run', 10 ) );
	$n = max( 1, min( 100, $n ) );
	return (int) apply_filters( 'bj_rss_max_per_run', $n );
}

/**
 * Load persisted RSS sync queue rows.
 *
 * @return array<int, array<string, mixed>>
 */
function bj_get_rss_sync_queue() {
	$q = get_option( 'bj_rss_sync_queue', array() );
	return is_array( $q ) ? array_values( $q ) : array();
}

/**
 * Persist RSS sync queue.
 *
 * @param array<int, array<string, mixed>> $queue Rows (FIFO).
 * @return void
 */
function bj_save_rss_sync_queue( array $queue ) {
	update_option( 'bj_rss_sync_queue', array_values( $queue ), false );
}

/**
 * Append rows to the RSS queue without duplicate check-in IDs (later rows win).
 *
 * @param array<int, array<string, mixed>> $queue Existing queue.
 * @param array<int, array<string, mixed>> $rows  Rows to append.
 * @return array<int, array<string, mixed>>
 */
function bj_rss_queue_merge_unique( array $queue, array $rows ) {
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
function bj_maybe_schedule_rss_queue_tick() {
	$q = bj_get_rss_sync_queue();
	if ( empty( $q ) ) {
		return;
	}
	$delay = max( 60, absint( get_option( 'bj_scraping_delay', 3 ) ) );
	$group = bj_action_scheduler_group();

	if ( bj_using_action_scheduler() ) {
		if ( as_next_scheduled_action( 'bj_rss_queue_tick', array(), $group ) ) {
			return;
		}
		as_schedule_single_action( time() + $delay, 'bj_rss_queue_tick', array(), $group );
		return;
	}

	if ( wp_next_scheduled( 'bj_rss_queue_tick' ) ) {
		return;
	}
	wp_schedule_single_event( time() + $delay, 'bj_rss_queue_tick' );
}

/**
 * Whether the Action Scheduler API is available (WooCommerce or standalone plugin).
 *
 * @return bool
 */
function bj_using_action_scheduler() {
	return function_exists( 'as_schedule_recurring_action' )
		&& function_exists( 'as_next_scheduled_action' )
		&& function_exists( 'as_schedule_single_action' );
}

/**
 * Action Scheduler group for all Beer Journal jobs.
 *
 * @return string
 */
function bj_action_scheduler_group() {
	return 'beer-journal';
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
	delete_transient( 'bj_incomplete_checkin_count' );
}

/**
 * Approximate count of draft check-ins missing scraped data (cached briefly).
 *
 * @return int
 */
function bj_count_draft_incomplete_checkins() {
	if ( ! post_type_exists( BJ_Post_Type::POST_TYPE ) ) {
		return 0;
	}
	return (int) bj_get_cached_data(
		'incomplete_checkin_count',
		static function () {
			$q = new WP_Query(
				array(
					'post_type'              => BJ_Post_Type::POST_TYPE,
					'post_status'            => 'draft',
					'fields'                 => 'ids',
					'posts_per_page'         => 200,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'     => '_bj_incomplete_reason',
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
function bj_rescrape_checkin_post( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error( 'forbidden', __( 'You cannot edit this check-in.', 'beer-journal' ) );
	}
	if ( get_post_type( $post_id ) !== BJ_Post_Type::POST_TYPE ) {
		return new WP_Error( 'wrong_type', __( 'Not a beer check-in post.', 'beer-journal' ) );
	}
	$url = get_post_meta( $post_id, '_bj_checkin_url', true );
	if ( ! is_string( $url ) || '' === $url || false === strpos( $url, 'untappd.com' ) ) {
		return new WP_Error( 'no_url', __( 'No valid Untappd check-in URL on this post.', 'beer-journal' ) );
	}
	$cid = get_post_meta( $post_id, '_bj_checkin_id', true );
	$data = array(
		'checkin_id'   => is_string( $cid ) && '' !== $cid ? $cid : (string) bj_parse_checkin_id_from_url( $url ),
		'checkin_url'  => esc_url_raw( $url ),
		'checkin_date' => (string) get_post_meta( $post_id, '_bj_checkin_date', true ),
	);
	if ( '' === $data['checkin_id'] ) {
		return new WP_Error( 'no_id', __( 'Missing check-in ID.', 'beer-journal' ) );
	}
	$scraper = new BJ_Scraper();
	$scraped = $scraper->scrape_checkin_url( $url );
	if ( ! is_wp_error( $scraped ) ) {
		$data = array_merge( $data, $scraped );
	} else {
		BJ_Logger::warning( 'Re-scrape failed for post ' . $post_id . ': ' . $scraped->get_error_message() );
		return $scraped;
	}
	$importer = new BJ_Importer();
	return $importer->import_checkin_data( $data, 'rss' );
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
