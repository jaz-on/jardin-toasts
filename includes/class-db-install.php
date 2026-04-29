<?php
/**
 * Optional database optimizations (indexes) for large sites.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_DB_Install
 */
class JB_DB_Install {

	/**
	 * Add composite index on postmeta for check-in ID lookups (best-effort, once).
	 *
	 * @return void
	 */
	public static function maybe_add_indexes() {
		$state = get_option( 'jb_db_index_checkin_v1', '' );
		if ( in_array( $state, array( 'ok', 'failed' ), true ) ) {
			return;
		}

		global $wpdb;
		$wpdb->suppress_errors( true );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query(
			"ALTER TABLE {$wpdb->postmeta} ADD INDEX jb_checkin_meta (meta_key(20), meta_value(64))"
		);
		$wpdb->suppress_errors( false );

		$err = $wpdb->last_error;
		if ( ! $err ) {
			update_option( 'jb_db_index_checkin_v1', 'ok', true );
			return;
		}
		if ( false !== strpos( $err, 'Duplicate' ) || false !== strpos( $err, 'duplicate' ) ) {
			update_option( 'jb_db_index_checkin_v1', 'ok', true );
			return;
		}

		update_option( 'jb_db_index_checkin_v1', 'failed', true );
		JB_Logger::warning( 'DB index jb_checkin_meta not added: ' . $err );
	}
}
