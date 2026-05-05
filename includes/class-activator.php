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
 * Class Jardin_Toasts_Activator
 */
class Jardin_Toasts_Activator {

	/**
	 * Run on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		Jardin_Toasts_Storage_Migration::maybe_migrate();
		Jardin_Toasts_Settings::ensure_defaults();
		Jardin_Toasts_DB_Install::maybe_add_indexes();
		flush_rewrite_rules();
		do_action( 'jardin_toasts_plugin_activated' );
		do_action( 'jardin_toasts_plugin_activated' );
	}
}
