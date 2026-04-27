<?php
/**
 * Registers the beer_checkin custom post type.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Post_Type
 */
class BJ_Post_Type {

	/**
	 * Post type slug.
	 */
	public const POST_TYPE = 'beer_checkin';

	/**
	 * Top-level admin menu slug (see BJ_Admin::register_menu).
	 * CPT and taxonomies must use the same value so taxonomy screens nest under Beer Journal.
	 */
	public const ADMIN_MENU_SLUG = 'beer-journal';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register beer_checkin CPT.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Beer check-ins', 'beer-journal' ),
			'singular_name'      => __( 'Beer check-in', 'beer-journal' ),
			'menu_name'          => __( 'Beer check-ins', 'beer-journal' ),
			'add_new'            => __( 'Add check-in', 'beer-journal' ),
			'add_new_item'       => __( 'Add new check-in', 'beer-journal' ),
			'edit_item'          => __( 'Edit check-in', 'beer-journal' ),
			'new_item'           => __( 'New check-in', 'beer-journal' ),
			'view_item'          => __( 'View check-in', 'beer-journal' ),
			'search_items'       => __( 'Search check-ins', 'beer-journal' ),
			'not_found'          => __( 'No check-ins found', 'beer-journal' ),
			'not_found_in_trash' => __( 'No check-ins in trash', 'beer-journal' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => self::ADMIN_MENU_SLUG,
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => true,
			'has_archive'        => true,
			'rewrite'            => array(
				'slug'       => 'checkins',
				'with_front' => false,
			),
			'menu_position'      => 26,
			'menu_icon'          => 'dashicons-beer',
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
