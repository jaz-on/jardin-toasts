<?php
/**
 * Background jobs: Action Scheduler when available, else WP-Cron.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JT_Action_Scheduler
 */
class JT_Action_Scheduler {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
		add_action( Jardin_Toasts_Keys::HOOK_RSS_SYNC, array( $this, 'run_rss_sync' ) );
		add_action( Jardin_Toasts_Keys::HOOK_RSS_QUEUE_TICK, array( $this, 'run_rss_queue_tick' ) );
		add_action( Jardin_Toasts_Keys::HOOK_BACKGROUND_IMPORT_BATCH, array( $this, 'run_background_import_batch' ) );
		add_action( Jardin_Toasts_Keys::HOOK_DAILY_LOG_CLEANUP, array( $this, 'run_log_cleanup' ) );
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
				'display'  => __( 'Every 6 hours', 'jardin-toasts' ),
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
		if ( ! get_option( 'jt_sync_enabled', true ) ) {
			return;
		}
		$feed = jt_get_rss_feed_url();
		if ( ! is_string( $feed ) || '' === trim( $feed ) ) {
			return;
		}

		if ( jt_using_action_scheduler() ) {
			$this->maybe_schedule_events_action_scheduler();
			return;
		}

		if ( ! wp_next_scheduled( Jardin_Toasts_Keys::HOOK_RSS_SYNC ) ) {
			$this->reschedule_adaptive_wp_cron();
		}
		if ( ! wp_next_scheduled( Jardin_Toasts_Keys::HOOK_DAILY_LOG_CLEANUP ) ) {
			wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', Jardin_Toasts_Keys::HOOK_DAILY_LOG_CLEANUP );
		}
	}

	/**
	 * Schedule recurring jobs via Action Scheduler.
	 *
	 * @return void
	 */
	private function maybe_schedule_events_action_scheduler() {
		$group = jt_action_scheduler_group();

		// One-time-style cleanup: drop WP-Cron rows for legacy `jt_*` names when AS owns scheduling.
		foreach ( Jardin_Toasts_Keys::legacy_jt_cron_hooks() as $h ) {
			wp_clear_scheduled_hook( $h );
		}

		jt_when_action_scheduler_store_ready(
			function () use ( $group ) {
				if ( ! as_next_scheduled_action( Jardin_Toasts_Keys::HOOK_RSS_SYNC, array(), $group ) ) {
					$recurrence = self::get_adaptive_recurrence();
					$interval   = self::recurrence_interval_seconds( $recurrence );
					as_schedule_recurring_action( time() + MINUTE_IN_SECONDS, $interval, Jardin_Toasts_Keys::HOOK_RSS_SYNC, array(), $group );
					JT_Logger::info( 'RSS sync scheduled via Action Scheduler, interval seconds: ' . (string) $interval );
				}

				if ( ! as_next_scheduled_action( Jardin_Toasts_Keys::HOOK_DAILY_LOG_CLEANUP, array(), $group ) ) {
					as_schedule_recurring_action( time() + DAY_IN_SECONDS, DAY_IN_SECONDS, Jardin_Toasts_Keys::HOOK_DAILY_LOG_CLEANUP, array(), $group );
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
		$last = get_option( 'jt_last_checkin_date', '' );
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
		wp_clear_scheduled_hook( Jardin_Toasts_Keys::HOOK_RSS_SYNC );
		$recurrence = self::get_adaptive_recurrence();
		wp_schedule_event( time() + MINUTE_IN_SECONDS, $recurrence, Jardin_Toasts_Keys::HOOK_RSS_SYNC );
		JT_Logger::info( 'RSS sync scheduled with recurrence: ' . $recurrence );
	}

	/**
	 * Reschedule RSS sync with adaptive interval.
	 *
	 * @return void
	 */
	public function reschedule_adaptive() {
		if ( jt_using_action_scheduler() ) {
			$group    = jt_action_scheduler_group();
			$interval = self::recurrence_interval_seconds( self::get_adaptive_recurrence() );
			jt_when_action_scheduler_store_ready(
				function () use ( $group, $interval ) {
					as_unschedule_all_actions( Jardin_Toasts_Keys::HOOK_RSS_SYNC, array(), $group );
					as_schedule_recurring_action( time() + MINUTE_IN_SECONDS, $interval, Jardin_Toasts_Keys::HOOK_RSS_SYNC, array(), $group );
					JT_Logger::info( 'RSS sync rescheduled via Action Scheduler, interval seconds: ' . (string) $interval );
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
		if ( ! get_option( 'jt_sync_enabled', true ) ) {
			return;
		}
		$parser   = new JT_RSS_Parser();
		$importer = new JT_Importer();
		$result   = $parser->sync_new_items( $importer );
		if ( is_wp_error( $result ) ) {
			$msg = $result->get_error_message();
			JT_Logger::error( 'RSS sync failed: ' . $msg );
			jt_send_notification_email(
				'[Jardin Toasts] ' . __( 'RSS sync failed', 'jardin-toasts' ),
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
		if ( ! get_option( 'jt_sync_enabled', true ) ) {
			return;
		}
		$parser   = new JT_RSS_Parser();
		$importer = new JT_Importer();
		$result   = $parser->drain_queue_tick( $importer );
		if ( is_wp_error( $result ) ) {
			JT_Logger::error( 'RSS queue tick failed: ' . $result->get_error_message() );
		}
	}

	/**
	 * Background import batch.
	 *
	 * @return void
	 */
	public function run_background_import_batch() {
		$crawler = new JT_Crawler();
		$crawler->process_next_batch();
	}

	/**
	 * Remove old log files.
	 *
	 * @return void
	 */
	public function run_log_cleanup() {
		$dir = jt_get_log_directory();
		if ( ! $dir ) {
			return;
		}
		$days = absint( get_option( 'jt_log_retention_days', 30 ) );
		if ( 0 === $days ) {
			return;
		}
		$cutoff     = time() - ( $days * DAY_IN_SECONDS );
		$toast_logs = glob( $dir . 'jardin-toasts-*.log' );
		$legacy_logs = glob( $dir . 'jardin-beer-*.log' );
		$files       = array_merge(
			is_array( $toast_logs ) ? $toast_logs : array(),
			is_array( $legacy_logs ) ? $legacy_logs : array()
		);
		if ( empty( $files ) ) {
			return;
		}
		foreach ( $files as $file ) {
			if ( @filemtime( $file ) < $cutoff ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				wp_delete_file( $file );
			}
		}
	}
}
