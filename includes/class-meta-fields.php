<?php
/**
 * Registers post meta for beer_checkin REST and sanitization.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Meta_Fields
 */
class BJ_Meta_Fields {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/**
	 * Register visible meta keys.
	 *
	 * @return void
	 */
	public function register_meta() {
		$post_type = BJ_Post_Type::POST_TYPE;
		$keys      = array(
			'_bj_checkin_id'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_checkin_url'     => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
			'_bj_beer_name'       => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_brewery_name'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_beer_style'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_beer_abv'        => array( 'type' => 'number', 'sanitize_callback' => 'floatval' ),
			'_bj_beer_ibu'        => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'_bj_rating_raw'      => array( 'type' => 'number', 'sanitize_callback' => 'floatval' ),
			'_bj_rating_rounded'  => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'_bj_serving_type'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_checkin_date'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_venue_name'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_exclude_sync'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_incomplete_reason' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_bj_source'          => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
		);

		foreach ( $keys as $key => $schema ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => $schema['type'],
					'sanitize_callback' => $schema['sanitize_callback'],
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
