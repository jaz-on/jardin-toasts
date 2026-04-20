<?php
/**
 * WP-Cron scheduling for RSS sync and background tasks.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Action_Scheduler
 */
class BJ_Action_Scheduler {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
		add_action( 'bj_rss_sync', array( $this, 'run_rss_sync' ) );
		add_action( 'bj_background_import_batch', array( $this, 'run_background_import_batch' ) );
		add_action( 'bj_daily_log_cleanup', array( $this, 'run_log_cleanup' ) );
		add_action( 'init', array( $this, 'maybe_schedule_events' ), 30 );
	}

	/**
	 * Add sixhourly interval.
	 *
	 * @param array<string, array<string, mixed>> $schedules Schedules.
	 * @return array<string, array<string, mixed>>
	 */
	public function add_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['sixhourly'] ) ) {
			$schedules['sixhourly'] = array(
				'interval' => 6 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 6 hours', 'beer-journal' ),
			);
		}
		return $schedules;
	}

	/**
	 * Schedule cron hooks if missing.
	 *
	 * @return void
	 */
	public function maybe_schedule_events() {
		if ( ! get_option( 'bj_sync_enabled', true ) ) {
			return;
		}
		$feed = get_option( 'bj_rss_feed_url', '' );
		if ( ! is_string( $feed ) || '' === trim( $feed ) ) {
			return;
		}
		if ( ! wp_next_scheduled( 'bj_rss_sync' ) ) {
			$this->reschedule_adaptive();
		}
		if ( ! wp_next_scheduled( 'bj_daily_log_cleanup' ) ) {
			wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'bj_daily_log_cleanup' );
		}
	}

	/**
	 * Compute adaptive recurrence slug.
	 *
	 * @return string
	 */
	public static function get_adaptive_recurrence() {
		$last = get_option( 'bj_last_checkin_date', '' );
		if ( ! is_string( $last ) || '' === $last ) {
			return 'daily';
		}
		$ts = strtotime( $last );
		if ( ! $ts ) {
			return 'daily';
		}
		$days = ( time() - $ts ) / DAY_IN_SECONDS;
		if ( $days < 7 ) {
			return 'sixhourly';
		}
		if ( $days < 30 ) {
			return 'daily';
		}
		return 'weekly';
	}

	/**
	 * Clear and reschedule RSS sync with adaptive interval.
	 *
	 * @return void
	 */
	public function reschedule_adaptive() {
		wp_clear_scheduled_hook( 'bj_rss_sync' );
		$recurrence = self::get_adaptive_recurrence();
		wp_schedule_event( time() + MINUTE_IN_SECONDS, $recurrence, 'bj_rss_sync' );
		BJ_Logger::info( 'RSS sync scheduled with recurrence: ' . $recurrence );
	}

	/**
	 * Cron callback: sync RSS feed.
	 *
	 * @return void
	 */
	public function run_rss_sync() {
		if ( ! get_option( 'bj_sync_enabled', true ) ) {
			return;
		}
		$parser   = new BJ_RSS_Parser();
		$importer = new BJ_Importer();
		$result   = $parser->sync_new_items( $importer );
		if ( is_wp_error( $result ) ) {
			BJ_Logger::error( 'RSS sync failed: ' . $result->get_error_message() );
		}
		$this->reschedule_adaptive();
	}

	/**
	 * Placeholder for chained background import (used by crawler).
	 *
	 * @return void
	 */
	public function run_background_import_batch() {
		$crawler = new BJ_Crawler();
		$crawler->process_next_batch();
	}

	/**
	 * Remove old log files.
	 *
	 * @return void
	 */
	public function run_log_cleanup() {
		$dir = bj_get_log_directory();
		if ( ! $dir ) {
			return;
		}
		$days = absint( get_option( 'bj_log_retention_days', 30 ) );
		if ( 0 === $days ) {
			return;
		}
		$cutoff = time() - ( $days * DAY_IN_SECONDS );
		$files  = glob( $dir . 'beer-journal-*.log' );
		if ( ! is_array( $files ) ) {
			return;
		}
		foreach ( $files as $file ) {
			if ( @filemtime( $file ) < $cutoff ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				wp_delete_file( $file );
			}
		}
	}
}
