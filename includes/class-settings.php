<?php
/**
 * Plugin options defaults and Settings API registration.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Settings
 */
class BJ_Settings {

	/**
	 * Option group name.
	 */
	public const OPTION_GROUP = 'beer_journal';

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
		if ( '1' === get_option( 'bj_placeholder_toggle_migrated', '' ) ) {
			return;
		}
		if ( (int) get_option( 'bj_placeholder_image_id', 0 ) > 0 ) {
			update_option( 'bj_use_placeholder_image', true );
		}
		update_option( 'bj_placeholder_toggle_migrated', '1', false );
	}

	/**
	 * Default option values.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults() {
		return array(
			'bj_rss_feed_url'           => bj_get_default_rss_feed_url(),
			'bj_sync_enabled'           => true,
			'bj_last_checkin_date'      => '',
			'bj_last_imported_guid'     => '',
			'bj_untappd_username'       => bj_get_default_untappd_username(),
			'bj_excluded_checkins'      => array(),
			'bj_rating_rules'           => bj_get_default_rating_rules(),
			'bj_rating_labels'          => bj_get_default_rating_labels(),
			'bj_rating_rounding_enabled' => true,
			'bj_import_checkpoint'      => array(),
			'bj_import_batch_size'      => 25,
			'bj_import_delay'           => 3,
			'bj_import_mode'            => 'manual',
			'bj_import_images'          => true,
			'bj_scraping_delay'         => 3,
			'bj_rss_max_per_run'        => 10,
			'bj_schema_enabled'         => true,
			'bj_microformats_enabled'   => true,
			'bj_debug_mode'             => false,
			'bj_log_retention_days'     => 30,
			'bj_import_social_data'     => true,
			'bj_import_venues'          => true,
			'bj_notify_on_sync'         => false,
			'bj_notify_on_error'        => true,
			'bj_notification_email'     => '',
			'bj_archive_layout'         => 'grid',
			'bj_use_placeholder_image'  => true,
			'bj_placeholder_image_id'   => 0,
			'bj_last_rss_sync_at'       => '',
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
			case 'bj_rss_feed_url':
				return esc_url_raw( (string) $value );
			case 'bj_sync_enabled':
			case 'bj_rating_rounding_enabled':
			case 'bj_import_images':
			case 'bj_schema_enabled':
			case 'bj_microformats_enabled':
			case 'bj_debug_mode':
			case 'bj_import_social_data':
			case 'bj_import_venues':
			case 'bj_notify_on_sync':
			case 'bj_notify_on_error':
			case 'bj_use_placeholder_image':
				return (bool) $value;
			case 'bj_import_batch_size':
			case 'bj_import_delay':
			case 'bj_log_retention_days':
				return absint( $value );
			case 'bj_scraping_delay':
				return max( 1, absint( $value ) );
			case 'bj_rss_max_per_run':
				return max( 1, min( 100, absint( $value ) ) );
			case 'bj_excluded_checkins':
				return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
			case 'bj_rating_rules':
				return self::sanitize_rating_rules_value( $value );
			case 'bj_rating_labels':
				return self::sanitize_rating_labels_value( $value );
			case 'bj_import_checkpoint':
				return is_array( $value ) ? $value : array();
			case 'bj_untappd_username':
				return sanitize_user( (string) $value, true );
			case 'bj_import_mode':
				$v = sanitize_text_field( (string) $value );
				return in_array( $v, array( 'manual', 'background' ), true ) ? $v : 'manual';
			case 'bj_archive_layout':
				$v = sanitize_text_field( (string) $value );
				return in_array( $v, array( 'grid', 'table' ), true ) ? $v : 'grid';
			case 'bj_placeholder_image_id':
				return absint( $value );
			case 'bj_notification_email':
				$e = sanitize_email( (string) $value );
				return '' === $e ? '' : $e;
			case 'bj_last_rss_sync_at':
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
		$defaults = bj_get_default_rating_rules();
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
		$defaults = bj_get_default_rating_labels();
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
