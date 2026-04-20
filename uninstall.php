<?php
/**
 * Uninstall: remove plugin options.
 *
 * @package BeerJournal
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$defaults = array(
	'bj_rss_feed_url',
	'bj_sync_enabled',
	'bj_last_checkin_date',
	'bj_last_imported_guid',
	'bj_untappd_username',
	'bj_excluded_checkins',
	'bj_rating_rules',
	'bj_rating_labels',
	'bj_rating_rounding_enabled',
	'bj_import_checkpoint',
	'bj_import_batch_size',
	'bj_import_delay',
	'bj_import_mode',
	'bj_import_images',
	'bj_scraping_delay',
	'bj_schema_enabled',
	'bj_microformats_enabled',
	'bj_debug_mode',
	'bj_log_retention_days',
	'bj_import_social_data',
	'bj_import_venues',
);

foreach ( $defaults as $key ) {
	delete_option( $key );
}

wp_clear_scheduled_hook( 'bj_rss_sync' );
wp_clear_scheduled_hook( 'bj_background_import_batch' );
wp_clear_scheduled_hook( 'bj_daily_log_cleanup' );
