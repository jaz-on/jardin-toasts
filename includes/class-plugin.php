<?php
/**
 * Main plugin bootstrap.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Jardin_Toasts_Plugin
 */
class Jardin_Toasts_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Jardin_Toasts_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Jardin_Toasts_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks and subsystems.
	 *
	 * @return void
	 */
	public function init() {
		jardin_toasts_maybe_remove_scraper_artifacts();
		add_action( 'init', array( 'Jardin_Toasts_DB_Install', 'maybe_add_indexes' ), 1 );

		$post_type = new Jardin_Toasts_Post_Type();
		$post_type->register();

		$tax = new Jardin_Toasts_Taxonomies();
		$tax->register();

		$meta = new Jardin_Toasts_Meta_Fields();
		$meta->register();

		$settings = new Jardin_Toasts_Settings();
		$settings->register();

		$scheduler = new Jardin_Toasts_Action_Scheduler();
		$scheduler->register();

		if ( is_admin() ) {
			$admin = new Jardin_Toasts_Admin();
			$admin->register();
		}

		$public = new Jardin_Toasts_Public();
		$public->register();

		$blocks = new Jardin_Toasts_Blocks();
		$blocks->register();
	}
}
