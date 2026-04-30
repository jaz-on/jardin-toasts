<?php
/**
 * Registers the beer_checkin custom post type.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JT_Post_Type
 */
class JT_Post_Type {

	/**
	 * Post type slug.
	 */
	public const POST_TYPE = 'beer_checkin';

	/**
	 * Parent file for CPT + taxonomy admin submenus (same as native posts under "Posts").
	 * Must be `edit.php?post_type=…` so check-ins load first, then taxonomy screens nest correctly.
	 */
	public const ADMIN_MENU_SLUG = 'edit.php?post_type=beer_checkin';

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
			'name'               => __( 'Beer check-ins', 'jardin-toasts' ),
			'singular_name'      => __( 'Beer check-in', 'jardin-toasts' ),
			'menu_name'          => __( 'Toasts', 'jardin-toasts' ),
			'all_items'          => __( 'Beers', 'jardin-toasts' ),
			'add_new'            => __( 'Add beer', 'jardin-toasts' ),
			'add_new_item'       => __( 'Add new beer', 'jardin-toasts' ),
			'edit_item'          => __( 'Edit check-in', 'jardin-toasts' ),
			'new_item'           => __( 'New check-in', 'jardin-toasts' ),
			'view_item'          => __( 'View check-in', 'jardin-toasts' ),
			'search_items'       => __( 'Search check-ins', 'jardin-toasts' ),
			'not_found'          => __( 'No check-ins found', 'jardin-toasts' ),
			'not_found_in_trash' => __( 'No check-ins in trash', 'jardin-toasts' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
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
