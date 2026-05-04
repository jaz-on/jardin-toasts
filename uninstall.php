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
	'jt_rss_feed_url',
	'jt_sync_enabled',
	'jt_last_checkin_date',
	'jt_last_imported_guid',
	'jt_untappd_username',
	'jt_untappd_session_cookie',
	'jt_excluded_checkins',
	'jt_rating_rules',
	'jt_rating_labels',
	'jt_rating_rounding_enabled',
	'jt_import_checkpoint',
	'jt_import_batch_size',
	'jt_import_delay',
	'jt_import_mode',
	'jt_import_images',
	'jt_scraping_delay',
	'jt_rss_max_per_run',
	'jt_rss_sync_queue',
	'jt_schema_enabled',
	'jt_microformats_enabled',
	'jt_debug_mode',
	'jt_log_retention_days',
	'jt_import_social_data',
	'jt_import_venues',
	'jt_notify_on_sync',
	'jt_notify_on_error',
	'jt_notification_email',
	'jt_archive_layout',
	'jt_use_placeholder_image',
	'jt_placeholder_image_id',
	'jt_last_rss_sync_at',
	'jt_db_index_checkin_v1',
	'jt_placeholder_toggle_migrated',
	'jt_beer_journal_storage_imported_v1',
	'jt_jb_prefix_storage_migrated_v1',
	'jt_product_paths_migrated_v1',
	'jb_storage_migrated_v1',
	'jb_jardin_toasts_product_rename_v1',
	'jardin_toasts_cron_hooks_migrated_v1',
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
	'jt_rss_sync',
	'jt_rss_queue_tick',
	'jt_background_import_batch',
	'jt_daily_log_cleanup',
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
