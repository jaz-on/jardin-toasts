<?php
/**
 * Registers taxonomies for beer check-ins.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Jardin_Toasts_Taxonomies
 */
class Jardin_Toasts_Taxonomies {

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
			'name'          => __( 'Beer styles', 'jardin-toasts' ),
			'singular_name' => __( 'Beer style', 'jardin-toasts' ),
			'search_items'  => __( 'Search styles', 'jardin-toasts' ),
			'all_items'     => __( 'All styles', 'jardin-toasts' ),
			'edit_item'     => __( 'Edit style', 'jardin-toasts' ),
			'update_item'   => __( 'Update style', 'jardin-toasts' ),
			'add_new_item'  => __( 'Add new style', 'jardin-toasts' ),
			'new_item_name' => __( 'New style name', 'jardin-toasts' ),
			'menu_name'     => __( 'Styles', 'jardin-toasts' ),
		);

		register_taxonomy(
			self::STYLE,
			Jardin_Toasts_Post_Type::POST_TYPE,
			array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => Jardin_Toasts_Post_Type::ADMIN_MENU_SLUG,
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
			'name'          => __( 'Breweries', 'jardin-toasts' ),
			'singular_name' => __( 'Brewery', 'jardin-toasts' ),
			'search_items'  => __( 'Search breweries', 'jardin-toasts' ),
			'all_items'     => __( 'All breweries', 'jardin-toasts' ),
			'edit_item'     => __( 'Edit brewery', 'jardin-toasts' ),
			'update_item'   => __( 'Update brewery', 'jardin-toasts' ),
			'add_new_item'  => __( 'Add new brewery', 'jardin-toasts' ),
			'new_item_name' => __( 'New brewery name', 'jardin-toasts' ),
			'menu_name'     => __( 'Breweries', 'jardin-toasts' ),
		);

		register_taxonomy(
			self::BREWERY,
			Jardin_Toasts_Post_Type::POST_TYPE,
			array(
				'labels'            => $labels,
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => Jardin_Toasts_Post_Type::ADMIN_MENU_SLUG,
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
			'name'          => __( 'Venues', 'jardin-toasts' ),
			'singular_name' => __( 'Venue', 'jardin-toasts' ),
			'search_items'  => __( 'Search venues', 'jardin-toasts' ),
			'all_items'     => __( 'All venues', 'jardin-toasts' ),
			'edit_item'     => __( 'Edit venue', 'jardin-toasts' ),
			'update_item'   => __( 'Update venue', 'jardin-toasts' ),
			'add_new_item'  => __( 'Add new venue', 'jardin-toasts' ),
			'new_item_name' => __( 'New venue name', 'jardin-toasts' ),
			'menu_name'     => __( 'Venues', 'jardin-toasts' ),
		);

		register_taxonomy(
			self::VENUE,
			Jardin_Toasts_Post_Type::POST_TYPE,
			array(
				'labels'            => $labels,
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => Jardin_Toasts_Post_Type::ADMIN_MENU_SLUG,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'venue' ),
			)
		);
	}
}
