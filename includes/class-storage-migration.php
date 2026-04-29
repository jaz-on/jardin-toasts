<?php
/**
 * One-time migrations: beer-journal / bj_ → jb_ keys; theme paths in post content; product rename jardin-beer → jardin-toasts.
 *
 * @package JardinToasts
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
	 * Set after post content + Action Scheduler group cleanup for the jardin-toasts rename.
	 */
	public const PRODUCT_RENAME_FLAG = 'jb_jardin_toasts_product_rename_v1';

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
	 * Rename persisted block/theme paths and clear Action Scheduler jobs in the legacy `jardin-beer` group (superseded by `jardin-toasts`).
	 *
	 * @return void
	 */
	public static function maybe_migrate_product_rename() {
		if ( get_option( self::PRODUCT_RENAME_FLAG, '' ) ) {
			return;
		}

		global $wpdb;

		self::migrate_post_content_product_paths( $wpdb );
		self::clear_action_scheduler_group_jardin_beer();
		update_option( self::PRODUCT_RENAME_FLAG, '1', false );
	}

	/**
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function migrate_post_content_product_paths( $wpdb ) {
		$replacements = array(
			array( 'jardin-beer/', 'jardin-toasts/' ),
			array( '<!-- wp:jardin-beer/', '<!-- wp:jardin-toasts/' ),
			array( 'wp:jardin-beer/', 'wp:jardin-toasts/' ),
			array( 'wp-block-jardin-beer-', 'wp-block-jardin-toasts-' ),
		);
		foreach ( $replacements as $pair ) {
			$from = $pair[0];
			$to   = $pair[1];
			$like = '%' . $wpdb->esc_like( $from ) . '%';
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPlaceholder -- static pattern.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s",
					$from,
					$to,
					$like
				)
			);
		}
	}

	/**
	 * @return void
	 */
	private static function clear_action_scheduler_group_jardin_beer() {
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}
		$hooks = array( 'jb_rss_sync', 'jb_rss_queue_tick', 'jb_background_import_batch', 'jb_daily_log_cleanup' );
		$group = 'jardin-beer';
		jb_when_action_scheduler_store_ready(
			static function () use ( $hooks, $group ) {
				foreach ( $hooks as $hook ) {
					as_unschedule_all_actions( $hook, array(), $group );
				}
			}
		);
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
			"UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'beer-journal/', 'jardin-toasts/') WHERE post_content LIKE '%beer-journal/%'"
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
