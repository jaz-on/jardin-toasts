<?php
/**
 * Plugin activation.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Activator
 */
class JB_Activator {

	/**
	 * Run on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		JB_Storage_Migration::maybe_migrate();
		JB_Settings::ensure_defaults();
		JB_DB_Install::maybe_add_indexes();
		flush_rewrite_rules();
		do_action( 'jb_plugin_activated' );
	}
}
