<?php
/**
 * Register Gutenberg blocks (dynamic, server-rendered).
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Jardin_Toasts_Blocks
 */
class Jardin_Toasts_Blocks {

	/**
	 * Block slugs relative to blocks/.
	 *
	 * @var string[]
	 */
	private const BLOCKS = array(
		'checkin-card',
		'recent-checkins',
		'menu-display',
		'beer-stats',
		'recent-reviews',
		'styles-breakdown',
	);

	/**
	 * Register blocks on init.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_block_types' ) );
	}

	/**
	 * @return void
	 */
	public function register_block_types() {
		foreach ( self::BLOCKS as $slug ) {
			$dir = JARDIN_TOASTS_PLUGIN_DIR . 'blocks/' . $slug;
			if ( is_readable( $dir . '/block.json' ) ) {
				register_block_type( $dir );
			}
		}
	}
}
