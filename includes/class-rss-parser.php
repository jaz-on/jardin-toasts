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
	 * Fetch feed and import new check-ins.
	 *
	 * @param BJ_Importer $importer Importer instance.
	 * @return true|WP_Error
	 */
	public function sync_new_items( BJ_Importer $importer ) {
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
			bj_touch_last_rss_sync_time();
			return true;
		}

		$latest_guid = $items[0]->get_id();
		$last_guid   = get_option( 'bj_last_imported_guid', '' );
		if ( is_string( $last_guid ) && '' !== $last_guid && $latest_guid === $last_guid ) {
			BJ_Logger::info( 'RSS sync: no new check-ins (GUID match).' );
			bj_touch_last_rss_sync_time();
			return true;
		}

		$newest_first = $items;
		$to_import    = array();
		foreach ( $newest_first as $item ) {
			$link = $item->get_link();
			if ( ! is_string( $link ) || '' === $link ) {
				continue;
			}
			$checkin_id = bj_parse_checkin_id_from_url( $link );
			if ( ! $checkin_id ) {
				continue;
			}
			if ( bj_get_post_id_by_checkin_id( $checkin_id ) ) {
				continue;
			}
			$parsed = bj_parse_rss_item_title( $item->get_title() );
			$date   = $item->get_date( 'c' );
			$desc   = $item->get_description();
			$img    = $this->extract_image_from_description( $desc );

			$to_import[] = array(
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

		if ( empty( $to_import ) ) {
			update_option( 'bj_last_imported_guid', $latest_guid, false );
			BJ_Logger::info( 'RSS sync: all items already imported.' );
			bj_touch_last_rss_sync_time();
			return true;
		}

		$to_import = array_reverse( $to_import );

		$imported = 0;
		foreach ( $to_import as $row ) {
			$result = $importer->import_from_rss_row( $row );
			if ( is_wp_error( $result ) ) {
				BJ_Logger::warning( $result->get_error_message() );
			} else {
				++$imported;
			}
		}

		update_option( 'bj_last_imported_guid', $latest_guid, false );
		$last = end( $to_import );
		if ( isset( $last['checkin_date'] ) ) {
			update_option( 'bj_last_checkin_date', $last['checkin_date'], false );
		}

		BJ_Logger::info( 'RSS sync completed. Imported batch count: ' . count( $to_import ) );

		bj_touch_last_rss_sync_time();

		if ( $imported > 0 && get_option( 'bj_notify_on_sync', false ) ) {
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

		return true;
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
