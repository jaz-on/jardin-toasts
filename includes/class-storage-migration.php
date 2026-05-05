<?php
/**
 * One-time migrations: beer-journal / bj_ → jt_; legacy jb_ options/meta → jt_; product paths jardin-beer → jardin-toasts.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migrates wp_options, post meta, serialized blocks, clears legacy cron/AS hooks.
 */
class JT_Storage_Migration {

	/**
	 * Beer-journal / bj_ import completed (or skipped because legacy jb migration already ran historically).
	 */
	public const BEER_JOURNAL_FLAG = 'jt_beer_journal_storage_imported_v1';

	/**
	 * All `jb_*` options and `_jb_*` post meta copied/renamed to `jt_*` / `_jt_*`.
	 */
	public const JB_PREFIX_UPGRADE_FLAG = 'jt_jb_prefix_storage_migrated_v1';

	/**
	 * Post content path / block namespace rename from jardin-beer → jardin-toasts.
	 */
	public const PRODUCT_RENAME_FLAG = 'jt_product_paths_migrated_v1';

	/**
	 * CPT beer_checkin → checkin and meta _jt_* → _jardin_toasts_*.
	 */
	public const NOMENCLATURE_FLAG = 'jardin_toasts_nomenclature_migrated_v1';

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
	 * @return string[]
	 */
	private static function rss_hook_names() {
		return Jardin_Toasts_Keys::legacy_jt_and_jb_rss_hooks();
	}

	/**
	 * Import from beer-journal / bj_* once (or skip if an older plugin release already ran the jb-era migration).
	 *
	 * @return void
	 */
	public static function maybe_migrate() {
		if ( get_option( self::BEER_JOURNAL_FLAG, '' ) || get_option( 'jb_storage_migrated_v1', '' ) ) {
			return;
		}

		global $wpdb;

		self::migrate_options( $wpdb );
		self::migrate_post_meta( $wpdb );
		self::migrate_block_markup( $wpdb );
		self::clear_legacy_schedulers();
		self::delete_legacy_transients( $wpdb );

		update_option( self::BEER_JOURNAL_FLAG, '1', false );
	}

	/**
	 * Copy/rename all `jb_*` wp_options and `_jb_*` post meta to `jt_*` / `_jt_*` (sites upgraded from jb-prefixed releases).
	 *
	 * @return void
	 */
	public static function maybe_migrate_jb_prefix_storage_to_jt() {
		if ( get_option( self::JB_PREFIX_UPGRADE_FLAG, '' ) ) {
			return;
		}

		global $wpdb;

		self::migrate_jb_options_to_jt( $wpdb );
		self::migrate_jb_postmeta_to_jt( $wpdb );
		self::delete_jb_transients( $wpdb );
		self::clear_all_legacy_plugin_cron_and_as();

		update_option( self::JB_PREFIX_UPGRADE_FLAG, '1', false );
	}

	/**
	 * CPT beer_checkin → checkin; meta _jt_* → _jardin_toasts_*.
	 *
	 * @return void
	 */
	public static function maybe_migrate_nomenclature(): void {
		if ( get_option( self::NOMENCLATURE_FLAG, '' ) ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-off migration.
		$wpdb->query(
			"UPDATE {$wpdb->posts} SET post_type = 'checkin' WHERE post_type = 'beer_checkin'"
		);

		$like = $wpdb->esc_like( '_jt_' ) . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-off migration.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_key = REPLACE(meta_key, '_jt_', '_jardin_toasts_') WHERE meta_key LIKE %s",
				$like
			)
		);

		update_option( self::NOMENCLATURE_FLAG, '1', false );
	}

	/**
	 * Rename persisted block/theme paths and clear Action Scheduler jobs in the legacy `jardin-beer` group.
	 *
	 * @return void
	 */
	public static function maybe_migrate_product_rename() {
		if ( get_option( self::PRODUCT_RENAME_FLAG, '' ) || get_option( 'jb_jardin_toasts_product_rename_v1', '' ) ) {
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
		$hooks = self::rss_hook_names();
		$group = 'jardin-beer';
		jt_when_action_scheduler_store_ready(
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
			$new = 'jt_' . substr( $old, 3 );
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
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function migrate_jb_options_to_jt( $wpdb ) {
		$rows = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'jb\\_%'"
		);
		if ( ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $old ) {
			if ( ! is_string( $old ) || '' === $old || 0 !== strpos( $old, 'jb_' ) ) {
				continue;
			}
			$new = 'jt_' . substr( $old, 3 );
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
				"UPDATE {$wpdb->postmeta} SET meta_key = REPLACE(meta_key, '_bj_', '_jt_') WHERE meta_key LIKE %s",
				$like
			)
		);
	}

	/**
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function migrate_jb_postmeta_to_jt( $wpdb ) {
		$like = $wpdb->esc_like( '_jb_' ) . '%';
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_key = REPLACE(meta_key, '_jb_', '_jt_') WHERE meta_key LIKE %s",
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
		foreach ( self::rss_hook_names() as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}
		$hooks = array_merge( self::LEGACY_CRON_HOOKS, self::rss_hook_names() );
		jt_when_action_scheduler_store_ready(
			static function () use ( $hooks ) {
				foreach ( $hooks as $hook ) {
					as_unschedule_all_actions( $hook, array(), self::LEGACY_AS_GROUP );
				}
			}
		);
	}

	/**
	 * Clear WP-Cron and Action Scheduler entries for both jb_ and jt_ hook names across legacy groups.
	 *
	 * @return void
	 */
	private static function clear_all_legacy_plugin_cron_and_as() {
		foreach ( self::rss_hook_names() as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}
		$hooks  = self::rss_hook_names();
		$groups = array( 'beer-journal', 'jardin-beer', 'jardin-toasts' );
		jt_when_action_scheduler_store_ready(
			static function () use ( $hooks, $groups ) {
				foreach ( $groups as $group ) {
					foreach ( $hooks as $hook ) {
						as_unschedule_all_actions( $hook, array(), $group );
					}
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

	/**
	 * @param \wpdb $wpdb WordPress DB object.
	 * @return void
	 */
	private static function delete_jb_transients( $wpdb ) {
		$p1 = $wpdb->esc_like( '_transient_jb_' ) . '%';
		$p2 = $wpdb->esc_like( '_transient_timeout_jb_' ) . '%';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$p1,
				$p2
			)
		);
	}
}
