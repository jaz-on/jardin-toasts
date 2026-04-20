<?php
/**
 * Registers taxonomies for beer check-ins.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Taxonomies
 */
class BJ_Taxonomies {

	public const STYLE = 'beer_style';
	public const BREWERY = 'brewery';
	public const VENUE  = 'venue';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register all taxonomies.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		$this->register_beer_style();
		$this->register_brewery();
		$this->register_venue();
	}

	/**
	 * Beer style (hierarchical).
	 *
	 * @return void
	 */
	private function register_beer_style() {
		$labels = array(
			'name'          => __( 'Beer styles', 'beer-journal' ),
			'singular_name' => __( 'Beer style', 'beer-journal' ),
			'search_items'  => __( 'Search styles', 'beer-journal' ),
			'all_items'     => __( 'All styles', 'beer-journal' ),
			'edit_item'     => __( 'Edit style', 'beer-journal' ),
			'update_item'   => __( 'Update style', 'beer-journal' ),
			'add_new_item'  => __( 'Add new style', 'beer-journal' ),
			'new_item_name' => __( 'New style name', 'beer-journal' ),
			'menu_name'     => __( 'Styles', 'beer-journal' ),
		);

		register_taxonomy(
			self::STYLE,
			BJ_Post_Type::POST_TYPE,
			array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'beer-style' ),
			)
		);
	}

	/**
	 * Brewery (non-hierarchical).
	 *
	 * @return void
	 */
	private function register_brewery() {
		$labels = array(
			'name'          => __( 'Breweries', 'beer-journal' ),
			'singular_name' => __( 'Brewery', 'beer-journal' ),
			'search_items'  => __( 'Search breweries', 'beer-journal' ),
			'all_items'     => __( 'All breweries', 'beer-journal' ),
			'edit_item'     => __( 'Edit brewery', 'beer-journal' ),
			'update_item'   => __( 'Update brewery', 'beer-journal' ),
			'add_new_item'  => __( 'Add new brewery', 'beer-journal' ),
			'new_item_name' => __( 'New brewery name', 'beer-journal' ),
			'menu_name'     => __( 'Breweries', 'beer-journal' ),
		);

		register_taxonomy(
			self::BREWERY,
			BJ_Post_Type::POST_TYPE,
			array(
				'labels'            => $labels,
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'brewery' ),
			)
		);
	}

	/**
	 * Venue (non-hierarchical).
	 *
	 * @return void
	 */
	private function register_venue() {
		$labels = array(
			'name'          => __( 'Venues', 'beer-journal' ),
			'singular_name' => __( 'Venue', 'beer-journal' ),
			'search_items'  => __( 'Search venues', 'beer-journal' ),
			'all_items'     => __( 'All venues', 'beer-journal' ),
			'edit_item'     => __( 'Edit venue', 'beer-journal' ),
			'update_item'   => __( 'Update venue', 'beer-journal' ),
			'add_new_item'  => __( 'Add new venue', 'beer-journal' ),
			'new_item_name' => __( 'New venue name', 'beer-journal' ),
			'menu_name'     => __( 'Venues', 'beer-journal' ),
		);

		register_taxonomy(
			self::VENUE,
			BJ_Post_Type::POST_TYPE,
			array(
				'labels'            => $labels,
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'venue' ),
			)
		);
	}
}
