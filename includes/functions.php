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
