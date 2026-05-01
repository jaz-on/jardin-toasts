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
 * Class JT_Activator
 */
class JT_Activator {

	/**
	 * Run on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		JT_Storage_Migration::maybe_migrate();
		JT_Settings::ensure_defaults();
		JT_DB_Install::maybe_add_indexes();
		flush_rewrite_rules();
		do_action( 'jardin_toasts_plugin_activated' );
		do_action( 'jt_plugin_activated' );
	}
}
