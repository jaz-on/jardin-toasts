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
function jardin_toasts_parse_checkin_id_from_url( $url ) {
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
function jardin_toasts_get_default_rating_rules() {
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
function jardin_toasts_get_default_rating_labels() {
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
function jardin_toasts_get_rating_labels() {
	$defaults = jardin_toasts_get_default_rating_labels();
	$stored   = get_option( 'jardin_toasts_rating_labels', false );
	if ( false === $stored || ! is_array( $stored ) ) {
		return apply_filters( 'jardin_toasts_rating_labels', apply_filters( 'jardin_toasts_rating_labels', $defaults ) );
	}
	$out = array();
	for ( $i = 0; $i <= 5; $i++ ) {
		$out[ $i ] = isset( $stored[ $i ] ) && '' !== $stored[ $i ]
			? (string) $stored[ $i ]
			: ( isset( $defaults[ $i ] ) ? $defaults[ $i ] : '' );
	}
	return apply_filters( 'jardin_toasts_rating_labels', apply_filters( 'jardin_toasts_rating_labels', $out ) );
}

/**
 * Default Untappd RSS feed URL used when the option is not stored yet.
 *
 * Optional: define `JARDIN_TOASTS_RSS_FEED_URL` in wp-config.php to override the default feed URL.
 * Legacy `JT_RSS_FEED_URL` is still honored (with a deprecation notice for site admins) so users
 * who set it before the may-2026 rename keep a working override until they update wp-config.php.
 *
 * @return string
 */
function jardin_toasts_get_default_rss_feed_url() {
	$override = '';
	if ( defined( 'JARDIN_TOASTS_RSS_FEED_URL' ) && is_string( JARDIN_TOASTS_RSS_FEED_URL ) && '' !== trim( JARDIN_TOASTS_RSS_FEED_URL ) ) {
		$override = JARDIN_TOASTS_RSS_FEED_URL;
	} elseif ( defined( 'JT_RSS_FEED_URL' ) && is_string( JT_RSS_FEED_URL ) && '' !== trim( JT_RSS_FEED_URL ) ) {
		$override = JT_RSS_FEED_URL;
		// Surface a one-time admin notice so the user knows to rename the constant.
		add_action(
			'admin_notices',
			static function () {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				echo '<div class="notice notice-warning"><p>';
				echo wp_kses_post( __( '<strong>Jardin Toasts</strong>: la constante <code>JT_RSS_FEED_URL</code> est dépréciée. Renommez-la en <code>JARDIN_TOASTS_RSS_FEED_URL</code> dans <code>wp-config.php</code>.', 'jardin-toasts' ) );
				echo '</p></div>';
			}
		);
	}

	if ( '' !== $override ) {
		return apply_filters( 'jardin_toasts_default_rss_feed_url', esc_url_raw( $override ) );
	}

	return apply_filters(
		'jardin_toasts_default_rss_feed_url',
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
function jardin_toasts_get_rss_feed_url() {
	$stored = get_option( 'jardin_toasts_rss_feed_url', false );
	if ( false === $stored ) {
		return jardin_toasts_get_default_rss_feed_url();
	}
	return trim( (string) $stored );
}

/**
 * Default Untappd profile username (examples / convenience field).
 *
 * @return string
 */
function jardin_toasts_get_default_untappd_username() {
	return apply_filters( 'jardin_toasts_default_untappd_username', apply_filters( 'jardin_toasts_default_untappd_username', 'jaz_on' ) );
}

/**
 * Username field value: stored, or default when the option was never saved.
 *
 * @return string
 */
function jardin_toasts_get_untappd_username() {
	$stored = get_option( 'jardin_toasts_untappd_username', false );
	if ( false === $stored ) {
		return jardin_toasts_get_default_untappd_username();
	}
	return (string) $stored;
}

/**
 * User-Agent for outbound HTTP (optional media and integrations).
 *
 * Same filter order as other doubles (`jardin_toasts_*` then `jardin_toasts_*`).
 *
 * @return string
 */
function jardin_toasts_http_user_agent_string() {
	$default = 'Jardin Toasts/' . JARDIN_TOASTS_VERSION . '; ' . home_url( '/' );
	return (string) apply_filters(
		'jardin_toasts_http_user_agent',
		(string) apply_filters( 'jardin_toasts_http_user_agent', $default )
	);
}

/**
 * Extract Untappd profile username from an RSS feed URL.
 *
 * @param string $url RSS URL.
 * @return string Username slug or empty.
 */
function jardin_toasts_parse_username_from_rss_url( $url ) {
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
function jardin_toasts_get_checkin_archive_url() {
	$pt = 'checkin';
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
function jardin_toasts_normalize_imported_post_content( $content ) {
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
function jardin_toasts_map_rating_raw_to_rounded( $raw ) {
	if ( null === $raw || '' === $raw ) {
		return null;
	}
	$raw = floatval( $raw );
	$rules = get_option( 'jardin_toasts_rating_rules', jardin_toasts_get_default_rating_rules() );
	$rules = apply_filters( 'jardin_toasts_rating_rules', apply_filters( 'jardin_toasts_rating_rules', $rules ) );
	if ( ! is_array( $rules ) || empty( $rules ) ) {
		$rules = jardin_toasts_get_default_rating_rules();
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
function jardin_toasts_parse_rss_item_title( $title ) {
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
function jardin_toasts_get_log_directory() {
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
function jardin_toasts_get_post_id_by_checkin_id( $checkin_id ) {
	global $wpdb;
	$checkin_id = sanitize_text_field( (string) $checkin_id );
	if ( '' === $checkin_id ) {
		return 0;
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$post_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
			'_jardin_toasts_checkin_id',
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
function jardin_toasts_get_post_ids_by_checkin_ids( array $checkin_ids ) {
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
	$sql = "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_jardin_toasts_checkin_id' AND meta_value IN ({$placeholders})";
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
 * Max RSS import attempts per cron run. Manual sync uses a higher cap via filter.
 *
 * @param bool $manual True when triggered from admin AJAX.
 * @return int
 */
function jardin_toasts_get_rss_sync_max_per_run( $manual = false ) {
	if ( $manual ) {
		$max = (int) apply_filters( 'jardin_toasts_rss_manual_sync_max_items', apply_filters( 'jardin_toasts_rss_manual_sync_max_items', 500 ) );
		return max( 1, $max );
	}
	$n = class_exists( 'Jardin_Toasts_Settings' ) ? (int) Jardin_Toasts_Settings::get( 'jardin_toasts_rss_max_per_run' ) : absint( get_option( 'jardin_toasts_rss_max_per_run', 10 ) );
	$n = max( 1, min( 100, $n ) );
	return (int) apply_filters( 'jardin_toasts_rss_max_per_run', apply_filters( 'jardin_toasts_rss_max_per_run', $n ) );
}

/**
 * Load persisted RSS sync queue rows.
 *
 * @return array<int, array<string, mixed>>
 */
function jardin_toasts_get_rss_sync_queue() {
	$q = get_option( 'jardin_toasts_rss_sync_queue', array() );
	return is_array( $q ) ? array_values( $q ) : array();
}

/**
 * Persist RSS sync queue.
 *
 * @param array<int, array<string, mixed>> $queue Rows (FIFO).
 * @return void
 */
function jardin_toasts_save_rss_sync_queue( array $queue ) {
	update_option( 'jardin_toasts_rss_sync_queue', array_values( $queue ), false );
}

/**
 * Append rows to the RSS queue without duplicate check-in IDs (later rows win).
 *
 * @param array<int, array<string, mixed>> $queue Existing queue.
 * @param array<int, array<string, mixed>> $rows  Rows to append.
 * @return array<int, array<string, mixed>>
 */
function jardin_toasts_rss_queue_merge_unique( array $queue, array $rows ) {
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
function jardin_toasts_maybe_schedule_rss_queue_tick() {
	$q = jardin_toasts_get_rss_sync_queue();
	if ( empty( $q ) ) {
		return;
	}
	$delay = (int) apply_filters(
		'jardin_toasts_rss_queue_tick_delay_seconds',
		apply_filters( 'jardin_toasts_rss_queue_tick_delay_seconds', 60 )
	);
	$delay = max( 30, $delay );
	$group = jardin_toasts_action_scheduler_group();

	if ( jardin_toasts_using_action_scheduler() ) {
		jardin_toasts_when_action_scheduler_store_ready(
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
 * Run a callback once Action Scheduler has initialized its data store (AS 3.1.6+).
 * Calling as_* APIs earlier triggers _doing_it_wrong notices.
 *
 * @param callable():void $callback Callback using Action Scheduler APIs.
 * @return void
 */
function jardin_toasts_when_action_scheduler_store_ready( callable $callback ) {
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
function jardin_toasts_using_action_scheduler() {
	return function_exists( 'as_schedule_recurring_action' )
		&& function_exists( 'as_next_scheduled_action' )
		&& function_exists( 'as_schedule_single_action' );
}

/**
 * Action Scheduler group for all Jardin Toasts jobs.
 *
 * @return string
 */
function jardin_toasts_action_scheduler_group() {
	return 'jardin-toasts';
}

/**
 * Transient-backed cache helper (see docs/development/caching.md).
 *
 * @param string   $key      Short key (prefix jardin_toasts_ added).
 * @param callable $producer Callback returning data to cache.
 * @param int|null $ttl      TTL seconds; default 1 hour.
 * @return mixed
 */
function jardin_toasts_get_cached_data( $key, $producer, $ttl = null ) {
	$key       = preg_replace( '/[^a-z0-9_\-]/i', '', (string) $key );
	$cache_key = 'jardin_toasts_' . $key;
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
function jardin_toasts_invalidate_stats_cache() {
	delete_transient( 'jardin_toasts_global_stats' );
	delete_transient( 'jardin_toasts_incomplete_checkin_count' );
}

/**
 * Approximate count of draft check-ins missing required import fields (cached briefly).
 *
 * @return int
 */
function jardin_toasts_count_draft_incomplete_checkins() {
	if ( ! post_type_exists( Jardin_Toasts_Post_Type::POST_TYPE ) ) {
		return 0;
	}
	return (int) jardin_toasts_get_cached_data(
		'incomplete_checkin_count',
		static function () {
			$q = new WP_Query(
				array(
					'post_type'              => Jardin_Toasts_Post_Type::POST_TYPE,
					'post_status'            => 'draft',
					'fields'                 => 'ids',
					'posts_per_page'         => 200,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'     => '_jardin_toasts_incomplete_reason',
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
 * Cached post counts for beer_checkin.
 *
 * @return array{publish: int, draft: int}
 */
function jardin_toasts_get_global_stats() {
	return jardin_toasts_get_cached_data(
		'global_stats',
		function () {
			$counts = wp_count_posts( Jardin_Toasts_Post_Type::POST_TYPE );
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
function jardin_toasts_send_notification_email( $subject, $body, $type = 'error' ) {
	if ( 'sync' === $type && ! get_option( 'jardin_toasts_notify_on_sync', false ) ) {
		return;
	}
	if ( 'error' === $type && ! get_option( 'jardin_toasts_notify_on_error', true ) ) {
		return;
	}
	$to = get_option( 'jardin_toasts_notification_email', '' );
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
function jardin_toasts_touch_last_rss_sync_time() {
	update_option( 'jardin_toasts_last_rss_sync_at', gmdate( 'c' ), false );
}

/**
 * Archive layout: grid or table (option jardin_toasts_archive_layout).
 *
 * @return string grid|table
 */
function jardin_toasts_get_archive_layout() {
	$l = get_option( 'jardin_toasts_archive_layout', 'grid' );
	return in_array( $l, array( 'grid', 'table' ), true ) ? $l : 'grid';
}

/**
 * One-time cleanup after HTML scraping / profile crawl removal (queue + scheduled batches).
 *
 * @return void
 */
function jardin_toasts_maybe_remove_scraper_artifacts() {
	if ( get_option( 'jardin_toasts_no_scraper_v1', '' ) === '1' ) {
		return;
	}

	delete_option( 'jardin_toasts_import_checkpoint' );
	wp_clear_scheduled_hook( Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH );

	if ( function_exists( 'as_unschedule_all_actions' ) && function_exists( 'jardin_toasts_when_action_scheduler_store_ready' ) ) {
		jardin_toasts_when_action_scheduler_store_ready(
			static function () {
				$hook = Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH;
				$groups = array( jardin_toasts_action_scheduler_group(), 'beer-journal', 'jardin-beer' );
				foreach ( $groups as $group ) {
					as_unschedule_all_actions( $hook, array(), $group );
				}
			}
		);
	}

	update_option( 'jardin_toasts_no_scraper_v1', '1', false );
}

/**
 * Canonical public identifiers (cron, AJAX, nonces) and legacy `jardin_toasts_*` cron cleanup.
 */
final class Jardin_Toasts_Keys {

	public const OPTION_CRON_HOOKS_MIGRATED = 'jardin_toasts_cron_hooks_migrated_v1';

	public const NONCE_ADMIN_AJAX = 'jardin_toasts_admin';

	public const HOOK_RSS_SYNC = 'jardin_toasts_rss_sync';
	public const HOOK_RSS_QUEUE_TICK = 'jardin_toasts_rss_queue_tick';
	public const HOOK_BACKGROUND_IMPORT_BATCH = 'jardin_toasts_background_import_batch';
	public const HOOK_DAILY_LOG_CLEANUP = 'jardin_toasts_daily_log_cleanup';

	public const AJAX_SYNC_NOW = 'jardin_toasts_sync_now';
	public const AJAX_TEST_RSS = 'jardin_toasts_test_rss';
	public const AJAX_IMPORT_GDPR_CSV = 'jardin_toasts_import_gdpr_csv';

	/**
	 * @return list<string>
	 */
	public static function legacy_jt_cron_hooks(): array {
		return array(
			'jardin_toasts_rss_sync',
			'jardin_toasts_rss_queue_tick',
			'jardin_toasts_background_import_batch',
			'jardin_toasts_daily_log_cleanup',
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

		if ( function_exists( 'jardin_toasts_using_action_scheduler' ) && jardin_toasts_using_action_scheduler() && function_exists( 'jardin_toasts_when_action_scheduler_store_ready' ) ) {
			jardin_toasts_when_action_scheduler_store_ready( $finish );
			return;
		}

		$finish();
	}
}
