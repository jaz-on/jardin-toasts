<?php
/**
 * Plugin deactivation.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JT_Deactivator
 */
class JT_Deactivator {

	/**
	 * Run on deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		$group          = function_exists( 'jt_action_scheduler_group' ) ? jt_action_scheduler_group() : 'jardin-toasts';
		$legacy_groups  = array( 'beer-journal', 'jardin-beer' );
		$hooks        = Jardin_Toasts_Keys::all_teardown_cron_hooks();
		$legacy_hooks = array( 'bj_rss_sync', 'bj_rss_queue_tick', 'bj_background_import_batch', 'bj_daily_log_cleanup' );
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			jt_when_action_scheduler_store_ready(
				static function () use ( $hooks, $legacy_hooks, $group, $legacy_groups ) {
					foreach ( $hooks as $hook ) {
						as_unschedule_all_actions( $hook, array(), $group );
					}
					foreach ( $legacy_groups as $legacy_group ) {
						foreach ( $hooks as $hook ) {
							as_unschedule_all_actions( $hook, array(), $legacy_group );
						}
					}
					foreach ( $legacy_hooks as $hook ) {
						as_unschedule_all_actions( $hook, array(), 'beer-journal' );
					}
				}
			);
		}
		foreach ( array_merge( $hooks, $legacy_hooks ) as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}
		flush_rewrite_rules();
		do_action( 'jardin_toasts_plugin_deactivated' );
		do_action( 'jt_plugin_deactivated' );
	}
}
