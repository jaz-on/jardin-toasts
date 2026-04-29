<?php
/**
 * Registers post meta for beer_checkin REST and sanitization.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Meta_Fields
 */
class JB_Meta_Fields {

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
		$post_type = JB_Post_Type::POST_TYPE;
		$keys      = array(
			'_jb_checkin_id'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_checkin_url'     => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
			'_jb_beer_name'       => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_brewery_name'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_beer_style'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_beer_abv'        => array( 'type' => 'number', 'sanitize_callback' => 'floatval' ),
			'_jb_beer_ibu'        => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'_jb_rating_raw'      => array( 'type' => 'number', 'sanitize_callback' => 'floatval' ),
			'_jb_rating_rounded'  => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'_jb_serving_type'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_checkin_date'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_venue_name'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_exclude_sync'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_incomplete_reason' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_jb_source'          => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
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
