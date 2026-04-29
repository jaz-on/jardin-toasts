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
 * Class JB_Blocks
 */
class JB_Blocks {

	/**
	 * Block slugs relative to blocks/.
	 *
	 * @var string[]
	 */
	private const BLOCKS = array(
		'checkin-card',
		'recent-checkins',
		'menu-display',
		'brewery-stats',
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
			$dir = JB_PLUGIN_DIR . 'blocks/' . $slug;
			if ( is_readable( $dir . '/block.json' ) ) {
				register_block_type( $dir );
			}
		}
	}
}
