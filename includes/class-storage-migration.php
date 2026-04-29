<?php
/**
 * One-time migration from beer-journal / bj_ storage to jardin-beer / jb_ keys.
 *
 * @package JardinBeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migrates wp_options, post meta, serialized blocks, clears legacy cron/AS hooks.
 */
class JB_Storage_Migration {

	public const FLAG_OPTION = 'jb_storage_migrated_v1';

	/**
	 * Legacy Action Scheduler group slug.
	 */
	private const LEGACY_AS_GROUP = 'beer-journal';

	/**
	 * @var string[]
	 */
	private const LEGACY_CRON_HOOKS = array(
		'bj_rss_sync',
		'bj_rss_queue_tick',
		'bj_background_import_batch',
		'bj_daily_log_cleanup',
	);

	/**
	 * Run once per site (sets {@see FLAG_OPTION}).
	 *
	 * @return void
	 */
	public static function maybe_migrate() {
		if ( get_option( self::FLAG_OPTION, '' ) ) {
			return;
		}

		global $wpdb;

		self::migrate_options( $wpdb );
		self::migrate_post_meta( $wpdb );
		self::migrate_block_markup( $wpdb );
		self::clear_legacy_schedulers();
		self::delete_legacy_transients( $wpdb );

		update_option( self::FLAG_OPTION, '1', false );
	}

	/**
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function migrate_options( $wpdb ) {
		$rows = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'bj\\_%'"
		);
		if ( ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $old ) {
			if ( ! is_string( $old ) || '' === $old || 0 !== strpos( $old, 'bj_' ) ) {
				continue;
			}
			$new = 'jb_' . substr( $old, 3 );
			$raw = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $old ) );
			if ( null === $raw ) {
				continue;
			}
			$val = maybe_unserialize( $raw );
			if ( ! self::option_row_exists( $wpdb, $new ) ) {
				add_option( $new, $val, '', false );
			} else {
				update_option( $new, $val, false );
			}
			delete_option( $old );
		}
	}

	/**
	 * @param \wpdb  $wpdb WordPress DB object.
	 * @param string $name Option name.
	 * @return bool
	 */
	private static function option_row_exists( $wpdb, $name ) {
		$n = $wpdb->get_var( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $name ) );
		return is_string( $n ) && $n === $name;
	}

	/**
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function migrate_post_meta( $wpdb ) {
		$like = $wpdb->esc_like( '_bj_' ) . '%';
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_key = REPLACE(meta_key, '_bj_', '_jb_') WHERE meta_key LIKE %s",
				$like
			)
		);
	}

	/**
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function migrate_block_markup( $wpdb ) {
		$wpdb->query(
			"UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'beer-journal/', 'jardin-beer/') WHERE post_content LIKE '%beer-journal/%'"
		);
	}

	/**
	 * @return void
	 */
	private static function clear_legacy_schedulers() {
		foreach ( self::LEGACY_CRON_HOOKS as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}
		$hooks = array_merge( self::LEGACY_CRON_HOOKS, array( 'jb_rss_sync', 'jb_rss_queue_tick', 'jb_background_import_batch', 'jb_daily_log_cleanup' ) );
		jb_when_action_scheduler_store_ready(
			static function () use ( $hooks ) {
				foreach ( $hooks as $hook ) {
					as_unschedule_all_actions( $hook, array(), self::LEGACY_AS_GROUP );
				}
			}
		);
	}

	/**
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function delete_legacy_transients( $wpdb ) {
		$p1 = $wpdb->esc_like( '_transient_bj_' ) . '%';
		$p2 = $wpdb->esc_like( '_transient_timeout_bj_' ) . '%';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$p1,
				$p2
			)
		);
	}
}
