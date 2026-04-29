<?php
/**
 * Plugin options defaults and Settings API registration.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Settings
 */
class JB_Settings {

	/**
	 * Option group name.
	 */
	public const OPTION_GROUP = 'jardin_toasts';

	/**
	 * Options maintained only by importers / cron / AJAX — never posted from the settings form.
	 *
	 * If these were registered with {@see register_setting()} for the same group as the form,
	 * {@see options.php} would call {@see update_option()} with null for any key missing from POST
	 * and wipe RSS URL, import queue, sync cursors, etc. whenever another tab was saved.
	 */
	private const INTERNAL_OPTIONS = array(
		'jb_last_checkin_date',
		'jb_last_imported_guid',
		'jb_last_rss_sync_at',
		'jb_excluded_checkins',
		'jb_import_checkpoint',
	);

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'init', array( __CLASS__, 'maybe_migrate_placeholder_toggle' ), 2 );
	}

	/**
	 * One-time: if a placeholder attachment was saved before the toggle existed, enable the toggle.
	 *
	 * @return void
	 */
	public static function maybe_migrate_placeholder_toggle() {
		if ( '1' === get_option( 'jb_placeholder_toggle_migrated', '' ) ) {
			return;
		}
		if ( (int) get_option( 'jb_placeholder_image_id', 0 ) > 0 ) {
			update_option( 'jb_use_placeholder_image', true );
		}
		update_option( 'jb_placeholder_toggle_migrated', '1', false );
	}

	/**
	 * Default option values.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults() {
		return array(
			'jb_rss_feed_url'           => jb_get_default_rss_feed_url(),
			'jb_sync_enabled'           => true,
			'jb_last_checkin_date'      => '',
			'jb_last_imported_guid'     => '',
			'jb_untappd_username'       => jb_get_default_untappd_username(),
			'jb_excluded_checkins'      => array(),
			'jb_rating_rules'           => jb_get_default_rating_rules(),
			'jb_rating_labels'          => jb_get_default_rating_labels(),
			'jb_rating_rounding_enabled' => true,
			'jb_import_checkpoint'      => array(),
			'jb_import_batch_size'      => 25,
			'jb_import_delay'           => 3,
			'jb_import_mode'            => 'manual',
			'jb_import_images'          => true,
			'jb_scraping_delay'         => 3,
			'jb_rss_max_per_run'        => 10,
			'jb_schema_enabled'         => true,
			'jb_microformats_enabled'   => true,
			'jb_debug_mode'             => false,
			'jb_log_retention_days'     => 30,
			'jb_import_social_data'     => true,
			'jb_import_venues'          => true,
			'jb_notify_on_sync'         => false,
			'jb_notify_on_error'        => true,
			'jb_notification_email'     => '',
			'jb_archive_layout'         => 'grid',
			'jb_use_placeholder_image'  => true,
			'jb_placeholder_image_id'   => 0,
			'jb_last_rss_sync_at'       => '',
		);
	}

	/**
	 * Ensure defaults exist in wp_options.
	 *
	 * @return void
	 */
	public static function ensure_defaults() {
		foreach ( self::get_defaults() as $key => $value ) {
			if ( false === get_option( $key, false ) ) {
				add_option( $key, $value, '', false );
			}
		}
	}

	/**
	 * Register settings and sections (minimal; admin UI expands fields).
	 *
	 * @return void
	 */
	public function register_settings() {
		foreach ( array_keys( self::get_defaults() ) as $key ) {
			if ( in_array( $key, self::INTERNAL_OPTIONS, true ) ) {
				continue;
			}
			register_setting(
				self::OPTION_GROUP,
				$key,
				array(
					'sanitize_callback' => function ( $value ) use ( $key ) {
						return self::sanitize_value_for_key( $key, $value );
					},
				)
			);
		}
	}

	/**
	 * Sanitize option by key name.
	 *
	 * @param string $key Option name.
	 * @param mixed  $value Value.
	 * @return mixed
	 */
	public static function sanitize_value_for_key( $key, $value ) {
		switch ( $key ) {
			case 'jb_rss_feed_url':
				return esc_url_raw( (string) $value );
			case 'jb_sync_enabled':
			case 'jb_rating_rounding_enabled':
			case 'jb_import_images':
			case 'jb_schema_enabled':
			case 'jb_microformats_enabled':
			case 'jb_debug_mode':
			case 'jb_import_social_data':
			case 'jb_import_venues':
			case 'jb_notify_on_sync':
			case 'jb_notify_on_error':
			case 'jb_use_placeholder_image':
				return (bool) $value;
			case 'jb_import_batch_size':
			case 'jb_import_delay':
			case 'jb_log_retention_days':
				return absint( $value );
			case 'jb_scraping_delay':
				return max( 1, absint( $value ) );
			case 'jb_rss_max_per_run':
				return max( 1, min( 100, absint( $value ) ) );
			case 'jb_excluded_checkins':
				return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
			case 'jb_rating_rules':
				return self::sanitize_rating_rules_value( $value );
			case 'jb_rating_labels':
				return self::sanitize_rating_labels_value( $value );
			case 'jb_import_checkpoint':
				return is_array( $value ) ? $value : array();
			case 'jb_untappd_username':
				return sanitize_user( (string) $value, true );
			case 'jb_import_mode':
				$v = sanitize_text_field( (string) $value );
				return in_array( $v, array( 'manual', 'background' ), true ) ? $v : 'manual';
			case 'jb_archive_layout':
				$v = sanitize_text_field( (string) $value );
				return in_array( $v, array( 'grid', 'table' ), true ) ? $v : 'grid';
			case 'jb_placeholder_image_id':
				return absint( $value );
			case 'jb_notification_email':
				$e = sanitize_email( (string) $value );
				return '' === $e ? '' : $e;
			case 'jb_last_rss_sync_at':
				return sanitize_text_field( (string) $value );
			default:
				return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $value;
		}
	}

	/**
	 * Option value with registered default when the key is absent from the database.
	 *
	 * @param string $key Option name.
	 * @return mixed
	 */
	public static function get( $key ) {
		$defaults = self::get_defaults();
		$fallback = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : false;
		return get_option( $key, $fallback );
	}

	/**
	 * Sanitize rating band rules from settings form.
	 *
	 * @param mixed $value Posted value.
	 * @return array<int, array{min: float, max: float, round: int}>
	 */
	private static function sanitize_rating_rules_value( $value ) {
		$defaults = jb_get_default_rating_rules();
		if ( ! is_array( $value ) ) {
			return $defaults;
		}
		$out   = array();
		$count = count( $defaults );
		for ( $i = 0; $i < $count; $i++ ) {
			$def = $defaults[ $i ];
			if ( ! isset( $value[ $i ] ) || ! is_array( $value[ $i ] ) ) {
				$out[] = $def;
				continue;
			}
			$row   = $value[ $i ];
			$min   = isset( $row['min'] ) ? floatval( wp_unslash( $row['min'] ) ) : $def['min'];
			$max   = isset( $row['max'] ) ? floatval( wp_unslash( $row['max'] ) ) : $def['max'];
			$round = isset( $row['round'] ) ? absint( $row['round'] ) : $def['round'];
			$round = min( 5, max( 0, $round ) );
			$out[] = array(
				'min'   => $min,
				'max'   => $max,
				'round' => $round,
			);
		}
		return $out;
	}

	/**
	 * Sanitize per-star labels (0–5).
	 *
	 * @param mixed $value Posted value.
	 * @return array<int, string>
	 */
	private static function sanitize_rating_labels_value( $value ) {
		$defaults = jb_get_default_rating_labels();
		if ( ! is_array( $value ) ) {
			return $defaults;
		}
		$out = array();
		for ( $i = 0; $i <= 5; $i++ ) {
			if ( ! isset( $value[ $i ] ) ) {
				$out[ $i ] = isset( $defaults[ $i ] ) ? $defaults[ $i ] : '';
				continue;
			}
			$out[ $i ] = sanitize_text_field( wp_unslash( (string) $value[ $i ] ) );
		}
		return $out;
	}
}
