<?php
/**
 * Main plugin bootstrap.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Plugin
 */
class BJ_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var BJ_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return BJ_Plugin
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
		add_action( 'init', array( 'BJ_DB_Install', 'maybe_add_indexes' ), 1 );

		$post_type = new BJ_Post_Type();
		$post_type->register();

		$tax = new BJ_Taxonomies();
		$tax->register();

		$meta = new BJ_Meta_Fields();
		$meta->register();

		$settings = new BJ_Settings();
		$settings->register();

		$scheduler = new BJ_Action_Scheduler();
		$scheduler->register();

		if ( is_admin() ) {
			$admin = new BJ_Admin();
			$admin->register();
		}

		$public = new BJ_Public();
		$public->register();

		$blocks = new BJ_Blocks();
		$blocks->register();
	}
}
