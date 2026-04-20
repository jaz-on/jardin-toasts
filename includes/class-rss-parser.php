<?php
/**
 * Untappd RSS feed parsing.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_RSS_Parser
 */
class BJ_RSS_Parser {

	/**
	 * Fetch feed and import new check-ins (and drain persisted queue first).
	 *
	 * @param BJ_Importer          $importer Importer instance.
	 * @param array<string, mixed> $args     Optional. `manual` bool for admin sync (higher per-run cap).
	 * @return true|WP_Error
	 */
	public function sync_new_items( BJ_Importer $importer, array $args = array() ) {
		$manual = ! empty( $args['manual'] );
		$queue_only = ! empty( $args['queue_only'] );

		if ( $queue_only ) {
			return $this->run_queue_pass( $importer, $manual, false );
		}

		$url = bj_get_rss_feed_url();
		if ( ! is_string( $url ) || '' === trim( $url ) ) {
			return new WP_Error( 'no_feed', __( 'RSS feed URL is not configured.', 'beer-journal' ) );
		}

		if ( ! function_exists( 'fetch_feed' ) ) {
			require_once ABSPATH . WPINC . '/feed.php';
		}

		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) {
			return $feed;
		}

		$items = $feed->get_items( 0, 50 );
		if ( empty( $items ) ) {
			BJ_Logger::info( 'RSS sync: no items in feed.' );
			$pass = $this->run_queue_pass( $importer, $manual, true );
			if ( is_wp_error( $pass ) ) {
				return $pass;
			}
			bj_touch_last_rss_sync_time();
			return true;
		}

		$latest_guid = $items[0]->get_id();

		$checkin_ids = array();
		$candidates  = array();
		foreach ( $items as $item ) {
			$link = $item->get_link();
			if ( ! is_string( $link ) || '' === $link ) {
				continue;
			}
			$checkin_id = bj_parse_checkin_id_from_url( $link );
			if ( ! $checkin_id ) {
				continue;
			}
			$checkin_ids[] = $checkin_id;
			$parsed        = bj_parse_rss_item_title( $item->get_title() );
			$date          = $item->get_date( 'c' );
			$desc          = $item->get_description();
			$img           = $this->extract_image_from_description( $desc );
			$candidates[]  = array(
				'checkin_id'   => $checkin_id,
				'checkin_url'  => esc_url_raw( $link ),
				'title'        => sanitize_text_field( $item->get_title() ),
				'beer_name'    => $parsed['beer'],
				'brewery_name' => $parsed['brewery'],
				'venue_name'   => $parsed['venue'],
				'checkin_date' => $date ? $date : gmdate( 'c' ),
				'rss_image'    => $img,
			);
		}

		$existing_map = bj_get_post_ids_by_checkin_ids( $checkin_ids );
		$to_import    = array();
		foreach ( $candidates as $row ) {
			$cid = (string) $row['checkin_id'];
			if ( isset( $existing_map[ $cid ] ) ) {
				continue;
			}
			$to_import[] = $row;
		}

		if ( empty( $to_import ) ) {
			update_option( 'bj_last_imported_guid', $latest_guid, false );
			BJ_Logger::info( 'RSS sync: all feed items already imported.' );
			$pass = $this->run_queue_pass( $importer, $manual, true );
			if ( is_wp_error( $pass ) ) {
				return $pass;
			}
			bj_touch_last_rss_sync_time();
			return true;
		}

		$to_import = array_reverse( $to_import );

		$last_checkin_date = '';
		if ( ! empty( $to_import ) ) {
			$last_row_for_date = $to_import[ count( $to_import ) - 1 ];
			if ( is_array( $last_row_for_date ) && isset( $last_row_for_date['checkin_date'] ) ) {
				$last_checkin_date = (string) $last_row_for_date['checkin_date'];
			}
		}

		$queue = bj_get_rss_sync_queue();
		$max   = bj_get_rss_sync_max_per_run( $manual );

		list( $imported, $queue ) = $this->import_until_budget( $importer, $queue, $to_import, $max );

		bj_save_rss_sync_queue( $queue );

		update_option( 'bj_last_imported_guid', $latest_guid, false );
		if ( '' !== $last_checkin_date ) {
			update_option( 'bj_last_checkin_date', $last_checkin_date, false );
		}

		$depth_after = count( bj_get_rss_sync_queue() );
		BJ_Logger::info(
			sprintf(
				'RSS sync: imported %1$d this run; queue depth %2$d (markup v%3$d).',
				$imported,
				$depth_after,
				BJ_Scraper_Config::MARKUP_VERSION
			)
		);

		bj_touch_last_rss_sync_time();

		bj_maybe_schedule_rss_queue_tick();

		$this->maybe_notify_sync( $imported, $manual );

		return true;
	}

	/**
	 * Cron: drain queue only (no RSS fetch). Does not call reschedule_adaptive.
	 *
	 * @param BJ_Importer $importer Importer.
	 * @return true|WP_Error
	 */
	public function drain_queue_tick( BJ_Importer $importer ) {
		return $this->run_queue_pass( $importer, false, true );
	}

	/**
	 * Drain persisted queue up to budget, optionally continuing into RSS candidates in one pass.
	 *
	 * @param BJ_Importer $importer     Importer.
	 * @param bool        $manual       Manual sync cap.
	 * @param bool        $touch_sync_time When true and work ran, update last sync time.
	 * @return true|WP_Error
	 */
	private function run_queue_pass( BJ_Importer $importer, $manual, $touch_sync_time ) {
		$queue = bj_get_rss_sync_queue();
		if ( empty( $queue ) ) {
			return true;
		}
		$max = bj_get_rss_sync_max_per_run( $manual );
		list( $imported, $queue ) = $this->import_until_budget( $importer, $queue, array(), $max );
		bj_save_rss_sync_queue( $queue );
		$depth = count( $queue );
		BJ_Logger::info(
			sprintf(
				'RSS queue tick: imported %1$d; queue depth %2$d.',
				$imported,
				$depth
			)
		);
		if ( $imported > 0 && $touch_sync_time ) {
			bj_touch_last_rss_sync_time();
		}
		bj_maybe_schedule_rss_queue_tick();
		$this->maybe_notify_sync( $imported, $manual );
		return true;
	}

	/**
	 * Import from front of queue, then from $rss_rows, until $budget imports attempted.
	 *
	 * @param BJ_Importer                        $importer Importer.
	 * @param array<int, array<string, mixed>>  $queue    Persisted queue (modified).
	 * @param array<int, array<string, mixed>>  $rss_rows New rows oldest-first (remaining appended to queue).
	 * @param int                               $budget   Max import attempts.
	 * @return array{0: int, 1: array<int, array<string, mixed>>} imported count, updated queue.
	 */
	private function import_until_budget( BJ_Importer $importer, array $queue, array $rss_rows, $budget ) {
		$imported = 0;
		$budget   = max( 0, (int) $budget );

		while ( $budget > 0 && ! empty( $queue ) ) {
			$row = array_shift( $queue );
			if ( ! is_array( $row ) ) {
				--$budget;
				continue;
			}
			$result = $importer->import_from_rss_row( $row );
			if ( is_wp_error( $result ) ) {
				BJ_Logger::warning( $result->get_error_message() );
			} else {
				++$imported;
			}
			--$budget;
		}

		if ( $budget > 0 && ! empty( $rss_rows ) ) {
			$chunk = array_splice( $rss_rows, 0, $budget );
			foreach ( $chunk as $row ) {
				$result = $importer->import_from_rss_row( $row );
				if ( is_wp_error( $result ) ) {
					BJ_Logger::warning( $result->get_error_message() );
				} else {
					++$imported;
				}
			}
			$budget -= count( $chunk );
			if ( ! empty( $rss_rows ) ) {
				$queue = bj_rss_queue_merge_unique( $queue, $rss_rows );
			}
		}

		return array( $imported, $queue );
	}

	/**
	 * Email when sync notifications enabled (avoid spam on partial cron ticks).
	 *
	 * @param int  $imported Count imported this run.
	 * @param bool $manual   From admin.
	 * @return void
	 */
	private function maybe_notify_sync( $imported, $manual ) {
		if ( $imported <= 0 || ! get_option( 'bj_notify_on_sync', false ) ) {
			return;
		}
		$queue_empty = empty( bj_get_rss_sync_queue() );
		if ( ! $manual && ! $queue_empty ) {
			return;
		}
		bj_send_notification_email(
			'[Beer Journal] ' . __( 'RSS sync completed', 'beer-journal' ),
			sprintf(
				/* translators: %d: number of check-ins imported */
				__( 'Imported %d new check-in(s).', 'beer-journal' ),
				$imported
			),
			'sync'
		);
	}

	/**
	 * Extract first image URL from RSS item description HTML.
	 *
	 * @param string $html HTML fragment.
	 * @return string
	 */
	private function extract_image_from_description( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return '';
		}
		if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m ) ) {
			return esc_url_raw( $m[1] );
		}
		return '';
	}
}
