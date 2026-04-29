<?php
/**
 * Background jobs: Action Scheduler when available, else WP-Cron.
 *
 * @package JardinBeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Action_Scheduler
 */
class JB_Action_Scheduler {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
		add_action( 'jb_rss_sync', array( $this, 'run_rss_sync' ) );
		add_action( 'jb_rss_queue_tick', array( $this, 'run_rss_queue_tick' ) );
		add_action( 'jb_background_import_batch', array( $this, 'run_background_import_batch' ) );
		add_action( 'jb_daily_log_cleanup', array( $this, 'run_log_cleanup' ) );
		add_action( 'init', array( $this, 'maybe_schedule_events' ), 30 );
	}

	/**
	 * Add sixhourly interval (WP-Cron fallback).
	 *
	 * @param array<string, array<string, mixed>> $schedules Schedules.
	 * @return array<string, array<string, mixed>>
	 */
	public function add_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['sixhourly'] ) ) {
			$schedules['sixhourly'] = array(
				'interval' => 6 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 6 hours', 'jardin-beer' ),
			);
		}
		return $schedules;
	}

	/**
	 * Map adaptive recurrence slug to seconds (Action Scheduler).
	 *
	 * @param string $recurrence daily|sixhourly|weekly.
	 * @return int
	 */
	public static function recurrence_interval_seconds( $recurrence ) {
		switch ( $recurrence ) {
			case 'sixhourly':
				return 6 * HOUR_IN_SECONDS;
			case 'weekly':
				return WEEK_IN_SECONDS;
			case 'daily':
			default:
				return DAY_IN_SECONDS;
		}
	}

	/**
	 * Schedule jobs if missing.
	 *
	 * @return void
	 */
	public function maybe_schedule_events() {
		if ( ! get_option( 'jb_sync_enabled', true ) ) {
			return;
		}
		$feed = jb_get_rss_feed_url();
		if ( ! is_string( $feed ) || '' === trim( $feed ) ) {
			return;
		}

		if ( jb_using_action_scheduler() ) {
			$this->maybe_schedule_events_action_scheduler();
			return;
		}

		if ( ! wp_next_scheduled( 'jb_rss_sync' ) ) {
			$this->reschedule_adaptive_wp_cron();
		}
		if ( ! wp_next_scheduled( 'jb_daily_log_cleanup' ) ) {
			wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'jb_daily_log_cleanup' );
		}
	}

	/**
	 * Schedule recurring jobs via Action Scheduler.
	 *
	 * @return void
	 */
	private function maybe_schedule_events_action_scheduler() {
		$group = jb_action_scheduler_group();

		// One-time migration off WP-Cron.
		wp_clear_scheduled_hook( 'jb_rss_sync' );
		wp_clear_scheduled_hook( 'jb_daily_log_cleanup' );
		wp_clear_scheduled_hook( 'jb_rss_queue_tick' );
		wp_clear_scheduled_hook( 'jb_background_import_batch' );

		jb_when_action_scheduler_store_ready(
			function () use ( $group ) {
				if ( ! as_next_scheduled_action( 'jb_rss_sync', array(), $group ) ) {
					$recurrence = self::get_adaptive_recurrence();
					$interval   = self::recurrence_interval_seconds( $recurrence );
					as_schedule_recurring_action( time() + MINUTE_IN_SECONDS, $interval, 'jb_rss_sync', array(), $group );
					JB_Logger::info( 'RSS sync scheduled via Action Scheduler, interval seconds: ' . (string) $interval );
				}

				if ( ! as_next_scheduled_action( 'jb_daily_log_cleanup', array(), $group ) ) {
					as_schedule_recurring_action( time() + DAY_IN_SECONDS, DAY_IN_SECONDS, 'jb_daily_log_cleanup', array(), $group );
				}
			}
		);
	}

	/**
	 * Compute adaptive recurrence slug.
	 *
	 * @return string
	 */
	public static function get_adaptive_recurrence() {
		$last = get_option( 'jb_last_checkin_date', '' );
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
	 * Clear and reschedule RSS sync (WP-Cron).
	 *
	 * @return void
	 */
	private function reschedule_adaptive_wp_cron() {
		wp_clear_scheduled_hook( 'jb_rss_sync' );
		$recurrence = self::get_adaptive_recurrence();
		wp_schedule_event( time() + MINUTE_IN_SECONDS, $recurrence, 'jb_rss_sync' );
		JB_Logger::info( 'RSS sync scheduled with recurrence: ' . $recurrence );
	}

	/**
	 * Reschedule RSS sync with adaptive interval.
	 *
	 * @return void
	 */
	public function reschedule_adaptive() {
		if ( jb_using_action_scheduler() ) {
			$group    = jb_action_scheduler_group();
			$interval = self::recurrence_interval_seconds( self::get_adaptive_recurrence() );
			jb_when_action_scheduler_store_ready(
				function () use ( $group, $interval ) {
					as_unschedule_all_actions( 'jb_rss_sync', array(), $group );
					as_schedule_recurring_action( time() + MINUTE_IN_SECONDS, $interval, 'jb_rss_sync', array(), $group );
					JB_Logger::info( 'RSS sync rescheduled via Action Scheduler, interval seconds: ' . (string) $interval );
				}
			);
			return;
		}
		$this->reschedule_adaptive_wp_cron();
	}

	/**
	 * Cron / AS callback: sync RSS feed.
	 *
	 * @return void
	 */
	public function run_rss_sync() {
		if ( ! get_option( 'jb_sync_enabled', true ) ) {
			return;
		}
		$parser   = new JB_RSS_Parser();
		$importer = new JB_Importer();
		$result   = $parser->sync_new_items( $importer );
		if ( is_wp_error( $result ) ) {
			$msg = $result->get_error_message();
			JB_Logger::error( 'RSS sync failed: ' . $msg );
			jb_send_notification_email(
				'[Jardin Beer] ' . __( 'RSS sync failed', 'jardin-beer' ),
				$msg,
				'error'
			);
		}
		$this->reschedule_adaptive();
	}

	/**
	 * Drain RSS import queue.
	 *
	 * @return void
	 */
	public function run_rss_queue_tick() {
		if ( ! get_option( 'jb_sync_enabled', true ) ) {
			return;
		}
		$parser   = new JB_RSS_Parser();
		$importer = new JB_Importer();
		$result   = $parser->drain_queue_tick( $importer );
		if ( is_wp_error( $result ) ) {
			JB_Logger::error( 'RSS queue tick failed: ' . $result->get_error_message() );
		}
	}

	/**
	 * Background import batch.
	 *
	 * @return void
	 */
	public function run_background_import_batch() {
		$crawler = new JB_Crawler();
		$crawler->process_next_batch();
	}

	/**
	 * Remove old log files.
	 *
	 * @return void
	 */
	public function run_log_cleanup() {
		$dir = jb_get_log_directory();
		if ( ! $dir ) {
			return;
		}
		$days = absint( get_option( 'jb_log_retention_days', 30 ) );
		if ( 0 === $days ) {
			return;
		}
		$cutoff = time() - ( $days * DAY_IN_SECONDS );
		$files  = glob( $dir . 'jardin-beer-*.log' );
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
