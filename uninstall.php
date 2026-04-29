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
	'jb_rss_feed_url',
	'jb_sync_enabled',
	'jb_last_checkin_date',
	'jb_last_imported_guid',
	'jb_untappd_username',
	'jb_excluded_checkins',
	'jb_rating_rules',
	'jb_rating_labels',
	'jb_rating_rounding_enabled',
	'jb_import_checkpoint',
	'jb_import_batch_size',
	'jb_import_delay',
	'jb_import_mode',
	'jb_import_images',
	'jb_scraping_delay',
	'jb_rss_max_per_run',
	'jb_rss_sync_queue',
	'jb_schema_enabled',
	'jb_microformats_enabled',
	'jb_debug_mode',
	'jb_log_retention_days',
	'jb_import_social_data',
	'jb_import_venues',
	'jb_notify_on_sync',
	'jb_notify_on_error',
	'jb_notification_email',
	'jb_archive_layout',
	'jb_use_placeholder_image',
	'jb_placeholder_image_id',
	'jb_last_rss_sync_at',
	'jb_db_index_checkin_v1',
	'jb_placeholder_toggle_migrated',
	'jb_storage_migrated_v1',
	'jb_jardin_toasts_product_rename_v1',
);

foreach ( $defaults as $key ) {
	delete_option( $key );
}

$legacy_opts = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'bj\\_%'" );
if ( is_array( $legacy_opts ) ) {
	foreach ( $legacy_opts as $legacy_key ) {
		delete_option( $legacy_key );
	}
}

wp_clear_scheduled_hook( 'jb_rss_sync' );
wp_clear_scheduled_hook( 'jb_rss_queue_tick' );
wp_clear_scheduled_hook( 'jb_background_import_batch' );
wp_clear_scheduled_hook( 'jb_daily_log_cleanup' );
wp_clear_scheduled_hook( 'bj_rss_sync' );
wp_clear_scheduled_hook( 'bj_rss_queue_tick' );
wp_clear_scheduled_hook( 'bj_background_import_batch' );
wp_clear_scheduled_hook( 'bj_daily_log_cleanup' );
