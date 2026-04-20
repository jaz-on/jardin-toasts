<?php
/**
 * Plugin deactivation.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Deactivator
 */
class BJ_Deactivator {

	/**
	 * Run on deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'bj_rss_sync' );
		wp_clear_scheduled_hook( 'bj_background_import_batch' );
		wp_clear_scheduled_hook( 'bj_daily_log_cleanup' );
		flush_rewrite_rules();
		do_action( 'bj_plugin_deactivated' );
	}
}
