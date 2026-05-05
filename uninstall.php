<?php
/**
 * Uninstall: remove plugin options.
 *
 * @package JardinToasts
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$defaults = array(
	'jardin_toasts_rss_feed_url',
	'jardin_toasts_sync_enabled',
	'jardin_toasts_last_checkin_date',
	'jardin_toasts_last_imported_guid',
	'jardin_toasts_untappd_username',
	'jardin_toasts_untappd_session_cookie',
	'jardin_toasts_excluded_checkins',
	'jardin_toasts_rating_rules',
	'jardin_toasts_rating_labels',
	'jardin_toasts_rating_rounding_enabled',
	'jardin_toasts_import_checkpoint',
	'jardin_toasts_import_batch_size',
	'jardin_toasts_import_delay',
	'jardin_toasts_import_mode',
	'jardin_toasts_import_images',
	'jardin_toasts_scraping_delay',
	'jardin_toasts_rss_max_per_run',
	'jardin_toasts_rss_sync_queue',
	'jardin_toasts_schema_enabled',
	'jardin_toasts_microformats_enabled',
	'jardin_toasts_debug_mode',
	'jardin_toasts_log_retention_days',
	'jardin_toasts_import_social_data',
	'jardin_toasts_import_venues',
	'jardin_toasts_notify_on_sync',
	'jardin_toasts_notify_on_error',
	'jardin_toasts_notification_email',
	'jardin_toasts_archive_layout',
	'jardin_toasts_use_placeholder_image',
	'jardin_toasts_placeholder_image_id',
	'jardin_toasts_last_rss_sync_at',
	'jardin_toasts_db_index_checkin_v1',
	'jardin_toasts_placeholder_toggle_migrated',
	'jardin_toasts_beer_journal_storage_imported_v1',
	'jardin_toasts_jb_prefix_storage_migrated_v1',
	'jardin_toasts_product_paths_migrated_v1',
	'jb_storage_migrated_v1',
	'jb_jardin_toasts_product_rename_v1',
	'jardin_toasts_cron_hooks_migrated_v1',
	'jardin_toasts_no_scraper_v1',
);

foreach ( $defaults as $key ) {
	delete_option( $key );
}

$legacy_jb = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'jb\\_%'" );
if ( is_array( $legacy_jb ) ) {
	foreach ( $legacy_jb as $legacy_key ) {
		delete_option( $legacy_key );
	}
}

$legacy_opts = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'bj\\_%'" );
if ( is_array( $legacy_opts ) ) {
	foreach ( $legacy_opts as $legacy_key ) {
		delete_option( $legacy_key );
	}
}

$hooks = array(
	'jardin_toasts_rss_sync',
	'jardin_toasts_rss_queue_tick',
	'jardin_toasts_background_import_batch',
	'jardin_toasts_daily_log_cleanup',
	'jardin_toasts_rss_sync',
	'jardin_toasts_rss_queue_tick',
	'jardin_toasts_background_import_batch',
	'jardin_toasts_daily_log_cleanup',
	'jb_rss_sync',
	'jb_rss_queue_tick',
	'jb_background_import_batch',
	'jb_daily_log_cleanup',
	'bj_rss_sync',
	'bj_rss_queue_tick',
	'bj_background_import_batch',
	'bj_daily_log_cleanup',
);
foreach ( $hooks as $hook ) {
	wp_clear_scheduled_hook( $hook );
}
