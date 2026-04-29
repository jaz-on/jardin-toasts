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
 * Class JB_Plugin
 */
class JB_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var JB_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return JB_Plugin
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
		add_action( 'init', array( 'JB_DB_Install', 'maybe_add_indexes' ), 1 );

		$post_type = new JB_Post_Type();
		$post_type->register();

		$tax = new JB_Taxonomies();
		$tax->register();

		$meta = new JB_Meta_Fields();
		$meta->register();

		$settings = new JB_Settings();
		$settings->register();

		$scheduler = new JB_Action_Scheduler();
		$scheduler->register();

		if ( is_admin() ) {
			$admin = new JB_Admin();
			$admin->register();
		}

		$public = new JB_Public();
		$public->register();

		$blocks = new JB_Blocks();
		$blocks->register();
	}
}
