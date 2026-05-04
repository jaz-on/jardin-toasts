<?php
/**
 * Import check-ins from Untappd data-export CSV (privacy / GDPR bundle, Insider archive, etc.).
 *
 * Column names vary slightly by export; we map common synonyms to the importer payload.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JT_Gdpr_Csv_Importer
 */
class JT_Gdpr_Csv_Importer {

	/**
	 * Import from an open readable stream (file handle).
	 *
	 * @param resource $stream Readable stream.
	 * @return array{imported:int,skipped:int,errors:list<string>}
	 */
	public function import_stream( $stream ) {
		$imported = 0;
		$skipped  = 0;
		$errors   = array();

		if ( ! is_resource( $stream ) ) {
			return array(
				'imported' => 0,
				'skipped'  => 0,
				'errors'   => array( __( 'Invalid file stream.', 'jardin-toasts' ) ),
			);
		}

		$first = fgets( $stream );
		if ( false === $first ) {
			return array(
				'imported' => 0,
				'skipped'  => 0,
				'errors'   => array( __( 'Empty CSV file.', 'jardin-toasts' ) ),
			);
		}

		$delimiter = $this->detect_delimiter( $first );
		rewind( $stream );

		$headers = fgetcsv( $stream, 0, $delimiter );
		if ( ! is_array( $headers ) || empty( $headers ) ) {
			return array(
				'imported' => 0,
				'skipped'  => 0,
				'errors'   => array( __( 'Could not read CSV header row.', 'jardin-toasts' ) ),
			);
		}

		$headers = $this->sanitize_header_row( $headers );

		$importer = new JT_Importer();
		$row_num  = 1;
		$max_rows = (int) apply_filters( 'jardin_toasts_gdpr_csv_max_rows', apply_filters( 'jt_gdpr_csv_max_rows', 8000 ) );
		$max_rows = max( 100, min( 50000, $max_rows ) );

		while ( ( $row = fgetcsv( $stream, 0, $delimiter ) ) !== false ) {
			++$row_num;
			if ( $row_num > $max_rows + 1 ) {
				$errors[] = sprintf(
					/* translators: %d: max rows */
					__( 'Stopped after %d rows (safety limit). Re-import remaining lines in a second file if needed.', 'jardin-toasts' ),
					$max_rows
				);
				break;
			}
			if ( $this->row_is_empty( $row ) ) {
				continue;
			}

			$assoc = $this->combine_row( $headers, $row );
			$data = $this->map_row_to_import_data( $assoc );
			$data = apply_filters( 'jt_gdpr_csv_map_row', $data, $assoc, $row_num );
			$data = apply_filters( 'jardin_toasts_gdpr_csv_row', $data, $assoc, $row_num );

			if ( null === $data || ! is_array( $data ) ) {
				++$skipped;
				continue;
			}

			$res = $importer->import_checkin_data( $data, 'gdpr_csv' );
			if ( is_wp_error( $res ) ) {
				if ( count( $errors ) < 25 ) {
					$errors[] = sprintf(
						/* translators: 1: row number, 2: error message */
						__( 'Row %1$d: %2$s', 'jardin-toasts' ),
						$row_num,
						$res->get_error_message()
					);
				}
				++$skipped;
				continue;
			}

			++$imported;
		}

		return compact( 'imported', 'skipped', 'errors' );
	}

	/**
	 * @param string $first_line First line of file.
	 * @return string Single-byte delimiter.
	 */
	private function detect_delimiter( $first_line ) {
		$comma = substr_count( $first_line, ',' );
		$semi  = substr_count( $first_line, ';' );
		return $semi > $comma ? ';' : ',';
	}

	/**
	 * @param array<int,string|false|null> $headers Header cells.
	 * @return list<string>
	 */
	private function sanitize_header_row( array $headers ) {
		$out = array();
		foreach ( $headers as $i => $h ) {
			$s = is_string( $h ) ? trim( $h ) : '';
			if ( 0 === $i ) {
				$s = preg_replace( '/^\xEF\xBB\xBF/', '', $s );
			}
			$key = strtolower( $s );
			$key = preg_replace( '/\s+/', '_', $key );
			$key = str_replace( '-', '_', $key );
			$out[] = $key;
		}
		return $out;
	}

	/**
	 * @param array<int,mixed> $row CSV row.
	 * @return bool
	 */
	private function row_is_empty( array $row ) {
		foreach ( $row as $cell ) {
			if ( is_string( $cell ) && '' !== trim( $cell ) ) {
				return false;
			}
			if ( is_numeric( $cell ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param list<string>       $headers Lowercase header names.
	 * @param array<int,mixed> $row Values.
	 * @return array<string,string>
	 */
	private function combine_row( array $headers, array $row ) {
		$assoc = array();
		foreach ( $headers as $i => $key ) {
			if ( '' === $key ) {
				continue;
			}
			$val           = isset( $row[ $i ] ) ? $row[ $i ] : '';
			$assoc[ $key ] = is_string( $val ) ? $val : (string) $val;
		}
		return $assoc;
	}

	/**
	 * Map one associative CSV row to importer payload, or null to skip.
	 *
	 * @param array<string,string> $assoc Row keyed by header.
	 * @return array<string,mixed>|null
	 */
	private function map_row_to_import_data( array $assoc ) {
		$checkin_id = trim( (string) ( $assoc['checkin_id'] ?? '' ) );

		$checkin_url = trim( (string) ( $assoc['checkin_url'] ?? $assoc['untappd_url'] ?? '' ) );
		if ( '' === $checkin_id && '' !== $checkin_url ) {
			$parsed = jt_parse_checkin_id_from_url( $checkin_url );
			if ( $parsed ) {
				$checkin_id = $parsed;
			}
		}

		if ( '' === $checkin_id || ! ctype_digit( $checkin_id ) ) {
			return null;
		}

		if ( '' === $checkin_url ) {
			$user = jt_get_untappd_username();
			if ( '' !== $user ) {
				$checkin_url = 'https://untappd.com/user/' . rawurlencode( $user ) . '/checkin/' . $checkin_id;
			}
		}

		$beer_name    = trim( (string) ( $assoc['beer_name'] ?? $assoc['beer'] ?? '' ) );
		$brewery_name = trim( (string) ( $assoc['brewery_name'] ?? $assoc['brewery'] ?? '' ) );
		$beer_style   = trim( (string) ( $assoc['beer_type'] ?? $assoc['beer_style'] ?? $assoc['style'] ?? '' ) );
		$comment      = trim( (string) ( $assoc['comment'] ?? $assoc['checkin_comment'] ?? $assoc['notes'] ?? $assoc['description'] ?? '' ) );
		$venue_name   = trim( (string) ( $assoc['venue_name'] ?? $assoc['venue'] ?? '' ) );
		$serving_type = trim( (string) ( $assoc['serving_type'] ?? $assoc['serving style'] ?? '' ) );

		$rating_raw = null;
		foreach ( array( 'rating_score', 'user_rating', 'rating', 'your_rating', 'checkin_rating' ) as $rk ) {
			if ( ! isset( $assoc[ $rk ] ) ) {
				continue;
			}
			$rv = trim( (string) $assoc[ $rk ] );
			if ( '' === $rv || '-' === $rv ) {
				continue;
			}
			if ( is_numeric( $rv ) ) {
				$rating_raw = floatval( $rv );
				break;
			}
		}

		$created = trim( (string) ( $assoc['created_at'] ?? $assoc['checkin_date'] ?? $assoc['date'] ?? $assoc['time'] ?? '' ) );
		if ( '' === $created ) {
			$checkin_date = gmdate( 'c' );
		} else {
			$ts = strtotime( $created );
			$checkin_date = $ts ? gmdate( 'c', $ts ) : $created;
		}

		$data = array(
			'checkin_id'   => $checkin_id,
			'checkin_url'  => $checkin_url,
			'beer_name'    => $beer_name,
			'brewery_name' => $brewery_name,
			'beer_style'   => $beer_style,
			'comment'      => $comment,
			'checkin_date' => $checkin_date,
			'venue_name'   => $venue_name,
			'serving_type' => $serving_type,
			'source'       => 'gdpr_csv',
		);

		if ( null !== $rating_raw ) {
			$data['rating_raw'] = $rating_raw;
		}

		foreach ( array( 'beer_abv', 'abv' ) as $ak ) {
			if ( isset( $assoc[ $ak ] ) && '' !== trim( (string) $assoc[ $ak ] ) && is_numeric( $assoc[ $ak ] ) ) {
				$data['beer_abv'] = floatval( $assoc[ $ak ] );
				break;
			}
		}

		foreach ( array( 'beer_ibu', 'ibu' ) as $ik ) {
			if ( isset( $assoc[ $ik ] ) && '' !== trim( (string) $assoc[ $ik ] ) && is_numeric( $assoc[ $ik ] ) ) {
				$data['beer_ibu'] = absint( $assoc[ $ik ] );
				break;
			}
		}

		foreach ( array( 'photo_url', 'image_url', 'photo', 'checkin_photo' ) as $pk ) {
			if ( ! empty( $assoc[ $pk ] ) && filter_var( trim( $assoc[ $pk ] ), FILTER_VALIDATE_URL ) ) {
				$data['image_url'] = esc_url_raw( trim( $assoc[ $pk ] ) );
				break;
			}
		}

		return $data;
	}
}
