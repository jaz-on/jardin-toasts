<?php
/**
 * Plugin deactivation.
 *
 * @package JardinBeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Deactivator
 */
class JB_Deactivator {

	/**
	 * Run on deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		$group        = function_exists( 'jb_action_scheduler_group' ) ? jb_action_scheduler_group() : 'jardin-beer';
		$legacy_group = 'beer-journal';
		$hooks        = array( 'jb_rss_sync', 'jb_rss_queue_tick', 'jb_background_import_batch', 'jb_daily_log_cleanup' );
		$legacy_hooks = array( 'bj_rss_sync', 'bj_rss_queue_tick', 'bj_background_import_batch', 'bj_daily_log_cleanup' );
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			jb_when_action_scheduler_store_ready(
				static function () use ( $hooks, $legacy_hooks, $group, $legacy_group ) {
					foreach ( $hooks as $hook ) {
						as_unschedule_all_actions( $hook, array(), $group );
					}
					foreach ( $legacy_hooks as $hook ) {
						as_unschedule_all_actions( $hook, array(), $legacy_group );
					}
				}
			);
		}
		foreach ( array_merge( $hooks, $legacy_hooks ) as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}
		flush_rewrite_rules();
		do_action( 'jb_plugin_deactivated' );
	}
}
