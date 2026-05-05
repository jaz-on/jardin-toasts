<?php
/**
 * Plugin Name:       Jardin Toasts
 * Plugin URI:        https://github.com/jaz-on/jardin-toasts
 * Description:       Jardin · Untappd : check-ins bière (CPT), synchro et blocs.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Tested up to:      7.0
 * Requires PHP:      8.2
 * Author:            Jason Rouet
 * Author URI:        https://jasonrouet.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       jardin-toasts
 * GitHub Plugin URI: https://github.com/jaz-on/jardin-toasts
 * Primary Branch:    dev
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JARDIN_TOASTS_VERSION', '0.1.0' );
define( 'JARDIN_TOASTS_PLUGIN_FILE', __FILE__ );
define( 'JARDIN_TOASTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JARDIN_TOASTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JARDIN_TOASTS_GITHUB_URL', 'https://github.com/jaz-on/jardin-toasts' );
define( 'JARDIN_TOASTS_KOFI_URL', 'https://ko-fi.com/jasonrouet' );

/**
 * True only after vendor/autoload.php loaded without throwing (Composer platform check, etc.).
 *
 * @return bool
 */
function jardin_toasts_runtime_ready() {
	return defined( 'JARDIN_TOASTS_PLUGIN_RUNTIME_LOADED' ) && JARDIN_TOASTS_PLUGIN_RUNTIME_LOADED;
}

// Load before Composer so a stale classmap (paths to removed `* 2.php` files) cannot fatal the site.
foreach ( array(
	JARDIN_TOASTS_PLUGIN_DIR . 'includes/class-taxonomies.php',
	JARDIN_TOASTS_PLUGIN_DIR . 'includes/class-logger.php',
	JARDIN_TOASTS_PLUGIN_DIR . 'includes/class-meta-fields.php',
	JARDIN_TOASTS_PLUGIN_DIR . 'public/class-public.php',
) as $jardin_toasts_bootstrap_file ) {
	if ( is_readable( $jardin_toasts_bootstrap_file ) ) {
		require_once $jardin_toasts_bootstrap_file;
	}
}

$jardin_toasts_autoload = JARDIN_TOASTS_PLUGIN_DIR . 'vendor/autoload.php';
if ( is_readable( $jardin_toasts_autoload ) ) {
	$jardin_toasts_autoload_files = JARDIN_TOASTS_PLUGIN_DIR . 'vendor/composer/autoload_files.php';
	try {
		if ( is_readable( $jardin_toasts_autoload_files ) ) {
			$jardin_toasts_files_to_check = require $jardin_toasts_autoload_files;
			if ( is_array( $jardin_toasts_files_to_check ) ) {
				foreach ( $jardin_toasts_files_to_check as $jardin_toasts_required_file ) {
					if ( ! is_string( $jardin_toasts_required_file ) || '' === $jardin_toasts_required_file ) {
						continue;
					}
					if ( ! is_readable( $jardin_toasts_required_file ) ) {
						throw new \RuntimeException(
							sprintf(
								'Missing Composer dependency file: %s',
								$jardin_toasts_required_file
							)
						);
					}
				}
			}
		}
		require_once $jardin_toasts_autoload;
		define( 'JARDIN_TOASTS_PLUGIN_RUNTIME_LOADED', true );
	} catch ( \Throwable $jardin_toasts_load_error ) {
		define( 'JARDIN_TOASTS_PLUGIN_RUNTIME_LOADED', false );
		add_action(
			'admin_notices',
			static function () use ( $jardin_toasts_load_error ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				echo '<div class="notice notice-error"><p>';
				echo esc_html__( 'Jardin Toasts could not load its PHP dependencies.', 'jardin-toasts' );
				echo ' ';
				echo esc_html( $jardin_toasts_load_error->getMessage() );
				echo '</p></div>';
			}
		);
	}
} else {
	define( 'JARDIN_TOASTS_PLUGIN_RUNTIME_LOADED', false );
	add_action(
		'admin_notices',
		function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Jardin Toasts is missing the vendor/ folder (Composer packages). If you install from Git, use a branch or release that includes vendor, or run composer install --no-dev in the plugin directory.', 'jardin-toasts' );
			echo '</p></div>';
		}
	);
}

/**
 * Run activation after Composer bootstrap; abort if runtime is unusable.
 *
 * @return void
 */
function jardin_toasts_plugin_activate(): void {
	if ( ! jardin_toasts_runtime_ready() ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins( plugin_basename( JARDIN_TOASTS_PLUGIN_FILE ), true );
		wp_die(
			wp_kses_post(
				__( 'Jardin Toasts cannot activate because its PHP dependencies did not load. If you installed from Git, run <code>composer install --no-dev</code> in the plugin directory (or use a release that includes <code>vendor/</code>), then activate again.', 'jardin-toasts' )
			),
			esc_html__( 'Plugin activation error', 'jardin-toasts' ),
			array( 'response' => 500, 'back_link' => true )
		);
	}
	Jardin_Toasts_Activator::activate();
}

/**
 * Clear schedules on deactivate even when Composer failed (plugin may still have been listed active).
 *
 * @return void
 */
function jardin_toasts_plugin_deactivate(): void {
	require_once JARDIN_TOASTS_PLUGIN_DIR . 'includes/functions.php';
	require_once JARDIN_TOASTS_PLUGIN_DIR . 'includes/class-deactivator.php';
	Jardin_Toasts_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'jardin_toasts_plugin_activate' );
register_deactivation_hook( __FILE__, 'jardin_toasts_plugin_deactivate' );

/**
 * Load plugin text domain.
 *
 * Hooked on `init` (priority 0) so the locale resolved by Polylang on
 * multilingual sites is the one used to load the `.mo`. `plugins_loaded`
 * is too early when Polylang detects locale via cookie / URL between
 * `plugins_loaded` and `init`. The `change_locale` callback reloads the
 * textdomain if `switch_to_locale()` is called mid-request.
 *
 * @return void
 */
function jardin_toasts_load_textdomain() {
	load_plugin_textdomain(
		'jardin-toasts',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'jardin_toasts_load_textdomain', 0 );
add_action(
	'change_locale',
	static function () {
		unload_textdomain( 'jardin-toasts' );
		jardin_toasts_load_textdomain();
	}
);

/**
 * Bootstrap plugin.
 *
 * @return void
 */
function jardin_toasts_init_plugin() {
	if ( ! jardin_toasts_runtime_ready() ) {
		return;
	}
	if ( class_exists( 'Jardin_Toasts_Keys', false ) ) {
		Jardin_Toasts_Keys::maybe_migrate_cron_hook_names();
	}
	Jardin_Toasts_Storage_Migration::maybe_migrate();
	Jardin_Toasts_Storage_Migration::maybe_migrate_jb_prefix_storage_to_jt();
	Jardin_Toasts_Storage_Migration::maybe_migrate_product_rename();
	Jardin_Toasts_Storage_Migration::maybe_migrate_nomenclature();
	Jardin_Toasts_Plugin::instance()->init();
}
add_action( 'plugins_loaded', 'jardin_toasts_init_plugin', 1 );

/**
 * Register plugin list action and meta links.
 *
 * @return void
 */
function jardin_toasts_register_plugin_list_hooks() {
	if ( ! jardin_toasts_runtime_ready() ) {
		return;
	}
	add_filter( 'plugin_action_links_' . plugin_basename( JARDIN_TOASTS_PLUGIN_FILE ), 'jardin_toasts_plugin_action_links' );
	add_filter( 'plugin_row_meta', 'jardin_toasts_plugin_row_meta', 10, 2 );
}
add_action( 'admin_init', 'jardin_toasts_register_plugin_list_hooks' );

/**
 * Add Settings link to the plugin action row.
 *
 * @param array $links Existing action links.
 * @return array Modified action links.
 */
function jardin_toasts_plugin_action_links( $links ) {
	if ( ! class_exists( 'Jardin_Toasts_Admin', false ) ) {
		return $links;
	}
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( Jardin_Toasts_Admin::get_settings_url() ),
		esc_html__( 'Settings', 'jardin-toasts' )
	);
	array_unshift( $links, $settings_link );
	return $links;
}

/**
 * Add GitHub and Donate links to the plugin meta row.
 *
 * @param array  $plugin_meta An array of plugin row meta links.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @return array Plugin row meta links.
 */
function jardin_toasts_plugin_row_meta( $plugin_meta, $plugin_file ) {
	if ( plugin_basename( JARDIN_TOASTS_PLUGIN_FILE ) !== $plugin_file ) {
		return $plugin_meta;
	}

	$new_links = array(
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( JARDIN_TOASTS_GITHUB_URL ),
			esc_html__( 'GitHub', 'jardin-toasts' )
		),
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( JARDIN_TOASTS_KOFI_URL ),
			esc_html__( 'Donate', 'jardin-toasts' )
		),
	);

	return array_merge( $plugin_meta, $new_links );
}
