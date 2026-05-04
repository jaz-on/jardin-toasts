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
 * Class JT_Plugin
 */
class JT_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var JT_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return JT_Plugin
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
		jt_maybe_remove_scraper_artifacts();
		add_action( 'init', array( 'JT_DB_Install', 'maybe_add_indexes' ), 1 );

		$post_type = new JT_Post_Type();
		$post_type->register();

		$tax = new JT_Taxonomies();
		$tax->register();

		$meta = new JT_Meta_Fields();
		$meta->register();

		$settings = new JT_Settings();
		$settings->register();

		$scheduler = new JT_Action_Scheduler();
		$scheduler->register();

		if ( is_admin() ) {
			$admin = new JT_Admin();
			$admin->register();
		}

		$public = new JT_Public();
		$public->register();

		$blocks = new JT_Blocks();
		$blocks->register();
	}
}
