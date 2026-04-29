<?php
/**
 * Creates and updates beer_checkin posts from normalized data.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Importer
 */
class JB_Importer {

	/**
	 * Import a single row from RSS discovery (scrapes check-in page).
	 *
	 * @param array<string,mixed> $row Row data.
	 * @return int|WP_Error Post ID or error.
	 */
	public function import_from_rss_row( array $row ) {
		$url = isset( $row['checkin_url'] ) ? $row['checkin_url'] : '';
		if ( ! is_string( $url ) || '' === $url ) {
			return new WP_Error( 'no_url', __( 'Missing check-in URL.', 'jardin-toasts' ) );
		}

		$scraper = new JB_Scraper();
		$scraped = $scraper->scrape_checkin_url( $url );
		$merged  = $row;

		if ( ! is_wp_error( $scraped ) ) {
			$merged = array_merge( $row, $scraped );
		} else {
			JB_Logger::warning( 'Scrape failed for ' . $url . ': ' . $scraped->get_error_message() );
		}

		$merged['source'] = 'rss';
		return $this->import_checkin_data( $merged, 'rss' );
	}

	/**
	 * Import using fully merged data (e.g. historical crawler).
	 *
	 * @param array<string,mixed> $data Normalized data.
	 * @param string                $source rss|crawler.
	 * @return int|WP_Error
	 */
	public function import_checkin_data( array $data, $source = 'rss' ) {
		$checkin_id = isset( $data['checkin_id'] ) ? (string) $data['checkin_id'] : '';
		if ( '' === $checkin_id ) {
			return new WP_Error( 'no_id', __( 'Missing check-in ID.', 'jardin-toasts' ) );
		}

		$excluded = get_option( 'jb_excluded_checkins', array() );
		if ( is_array( $excluded ) && in_array( $checkin_id, $excluded, true ) ) {
			return new WP_Error( 'excluded', __( 'Check-in excluded by settings.', 'jardin-toasts' ) );
		}

		$existing = jb_get_post_id_by_checkin_id( $checkin_id );
		if ( $existing ) {
			$exclude = get_post_meta( $existing, '_jb_exclude_sync', true );
			if ( '1' === $exclude ) {
				return new WP_Error( 'excluded_meta', __( 'Post excluded from sync.', 'jardin-toasts' ) );
			}
		}

		$beer_name    = isset( $data['beer_name'] ) ? sanitize_text_field( (string) $data['beer_name'] ) : '';
		$brewery_name = isset( $data['brewery_name'] ) ? sanitize_text_field( (string) $data['brewery_name'] ) : '';
		$comment = isset( $data['comment'] ) ? wp_kses_post( (string) $data['comment'] ) : '';
		if ( '' === $comment && isset( $data['post_content'] ) ) {
			$comment = wp_kses_post( (string) $data['post_content'] );
		}
		$comment = jb_normalize_imported_post_content( $comment );

		$rating_raw = isset( $data['rating_raw'] ) && null !== $data['rating_raw'] ? floatval( $data['rating_raw'] ) : null;

		$checkin_date = isset( $data['checkin_date'] ) ? (string) $data['checkin_date'] : '';
		$ts           = strtotime( $checkin_date );
		if ( ! $ts ) {
			$ts = time();
		}

		$rounded = null !== $rating_raw ? jb_map_rating_raw_to_rounded( $rating_raw ) : null;

		$complete = ( '' !== $beer_name && '' !== $brewery_name && null !== $rating_raw );
		$status   = $complete ? 'publish' : 'draft';
		$reason   = '';
		if ( ! $complete ) {
			if ( null === $rating_raw ) {
				$reason = 'missing_rating';
			} elseif ( '' === $beer_name ) {
				$reason = 'missing_beer_name';
			} elseif ( '' === $brewery_name ) {
				$reason = 'missing_brewery_name';
			} else {
				$reason = 'incomplete';
			}
		}

		$title = $beer_name && $brewery_name ? $beer_name . ' - ' . $brewery_name : ( $beer_name ? $beer_name : __( 'Beer check-in', 'jardin-toasts' ) );

		$post_date_local = wp_date( 'Y-m-d H:i:s', $ts, wp_timezone() );
		$post_date_gmt   = get_gmt_from_date( $post_date_local );

		$postarr = array(
			'post_type'    => JB_Post_Type::POST_TYPE,
			'post_title'   => $title,
			'post_content' => $comment,
			'post_status'  => $status,
			'post_date'    => $post_date_local,
			'post_date_gmt' => $post_date_gmt,
		);

		if ( $existing ) {
			$postarr['ID'] = $existing;
		}

		do_action( 'jb_before_checkin_import', $data );

		$post_id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$this->save_meta( $post_id, $data, $rating_raw, $rounded, $checkin_date, $reason, $source );

		$this->assign_taxonomies( $post_id, $data );

		$img_url = '';
		if ( ! empty( $data['image_url'] ) ) {
			$img_url = (string) $data['image_url'];
		} elseif ( ! empty( $data['rss_image'] ) ) {
			$img_url = (string) $data['rss_image'];
		}

		if ( '' !== $img_url && get_option( 'jb_import_images', true ) ) {
			$images = new JB_Image_Handler();
			$res    = $images->import_for_post( $img_url, $post_id, $title );
			if ( is_wp_error( $res ) ) {
				JB_Logger::warning( 'Image import: ' . $res->get_error_message() );
			}
		}

		do_action( 'jb_after_checkin_imported', $post_id, $data );

		jb_invalidate_stats_cache();

		return (int) $post_id;
	}

	/**
	 * Save post meta fields.
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string,mixed>  $data Data.
	 * @param float|null           $rating_raw Raw rating.
	 * @param int|null             $rounded Rounded.
	 * @param string               $checkin_date ISO date string.
	 * @param string               $reason Incomplete reason.
	 * @param string               $source Source.
	 * @return void
	 */
	private function save_meta( $post_id, array $data, $rating_raw, $rounded, $checkin_date, $reason, $source ) {
		$fields = array(
			'_jb_checkin_id'    => isset( $data['checkin_id'] ) ? sanitize_text_field( (string) $data['checkin_id'] ) : '',
			'_jb_checkin_url'   => isset( $data['checkin_url'] ) ? esc_url_raw( (string) $data['checkin_url'] ) : '',
			'_jb_beer_name'     => isset( $data['beer_name'] ) ? sanitize_text_field( (string) $data['beer_name'] ) : '',
			'_jb_brewery_name'  => isset( $data['brewery_name'] ) ? sanitize_text_field( (string) $data['brewery_name'] ) : '',
			'_jb_beer_style'    => isset( $data['beer_style'] ) ? sanitize_text_field( (string) $data['beer_style'] ) : '',
			'_jb_serving_type'  => isset( $data['serving_type'] ) ? sanitize_text_field( (string) $data['serving_type'] ) : '',
			'_jb_venue_name'    => isset( $data['venue_name'] ) ? sanitize_text_field( (string) $data['venue_name'] ) : '',
			'_jb_checkin_date'  => sanitize_text_field( $checkin_date ),
			'_jb_source'        => sanitize_text_field( $source ),
			'_jb_scraped_at'    => gmdate( 'c' ),
		);

		if ( null !== $rating_raw ) {
			$fields['_jb_rating_raw']     = $rating_raw;
			$fields['_jb_rating_rounded'] = null !== $rounded ? $rounded : jb_map_rating_raw_to_rounded( $rating_raw );
		}

		if ( isset( $data['beer_abv'] ) && null !== $data['beer_abv'] ) {
			$fields['_jb_beer_abv'] = floatval( $data['beer_abv'] );
		}
		if ( isset( $data['beer_ibu'] ) && null !== $data['beer_ibu'] ) {
			$fields['_jb_beer_ibu'] = absint( $data['beer_ibu'] );
		}
		if ( isset( $data['toast_count'] ) && null !== $data['toast_count'] ) {
			$fields['_jb_toast_count'] = absint( $data['toast_count'] );
		}
		if ( isset( $data['comment_count'] ) && null !== $data['comment_count'] ) {
			$fields['_jb_comment_count'] = absint( $data['comment_count'] );
		}

		if ( '' !== $reason ) {
			$fields['_jb_incomplete_reason'] = $reason;
		} else {
			delete_post_meta( $post_id, '_jb_incomplete_reason' );
		}

		foreach ( $fields as $k => $v ) {
			update_post_meta( $post_id, $k, $v );
		}
	}

	/**
	 * Assign taxonomies from data.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string,mixed> $data Data.
	 * @return void
	 */
	private function assign_taxonomies( $post_id, array $data ) {
		if ( ! empty( $data['beer_style'] ) ) {
			wp_set_object_terms( $post_id, array( sanitize_text_field( (string) $data['beer_style'] ) ), JB_Taxonomies::STYLE, true );
		}
		if ( ! empty( $data['brewery_name'] ) ) {
			wp_set_object_terms( $post_id, array( sanitize_text_field( (string) $data['brewery_name'] ) ), JB_Taxonomies::BREWERY, true );
		}
		if ( ! empty( $data['venue_name'] ) && get_option( 'jb_import_venues', true ) ) {
			wp_set_object_terms( $post_id, array( sanitize_text_field( (string) $data['venue_name'] ) ), JB_Taxonomies::VENUE, true );
		}
	}
}
