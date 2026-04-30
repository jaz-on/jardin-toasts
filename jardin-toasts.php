<?php
/**
 * Plugin Name:       Jardin Toasts for Untappd
 * Plugin URI:        https://github.com/jaz-on/jardin-toasts
 * Description:       Syncs and displays your Untappd beer check-ins on WordPress with templates and media handling.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
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

define( 'JT_VERSION', '1.0.0' );
define( 'JT_PLUGIN_FILE', __FILE__ );
define( 'JT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JT_GITHUB_URL', 'https://github.com/jaz-on/jardin-toasts' );
define( 'JT_KOFI_URL', 'https://ko-fi.com/jasonrouet' );

/**
 * True only after vendor/autoload.php loaded without throwing (Composer platform check, etc.).
 *
 * @return bool
 */
function jt_runtime_ready() {
	return defined( 'JT_PLUGIN_RUNTIME_LOADED' ) && JT_PLUGIN_RUNTIME_LOADED;
}

// Load before Composer so a stale classmap (paths to removed `* 2.php` files) cannot fatal the site.
foreach ( array(
	JT_PLUGIN_DIR . 'includes/class-taxonomies.php',
	JT_PLUGIN_DIR . 'includes/class-logger.php',
	JT_PLUGIN_DIR . 'includes/class-meta-fields.php',
	JT_PLUGIN_DIR . 'public/class-public.php',
) as $jt_bootstrap_file ) {
	if ( is_readable( $jt_bootstrap_file ) ) {
		require_once $jt_bootstrap_file;
	}
}

$jt_autoload = JT_PLUGIN_DIR . 'vendor/autoload.php';
if ( is_readable( $jt_autoload ) ) {
	$jt_autoload_files = JT_PLUGIN_DIR . 'vendor/composer/autoload_files.php';
	try {
		if ( is_readable( $jt_autoload_files ) ) {
			$jt_files_to_check = require $jt_autoload_files;
			if ( is_array( $jt_files_to_check ) ) {
				foreach ( $jt_files_to_check as $jt_required_file ) {
					if ( ! is_string( $jt_required_file ) || '' === $jt_required_file ) {
						continue;
					}
					if ( ! is_readable( $jt_required_file ) ) {
						throw new \RuntimeException(
							sprintf(
								/* translators: %s: missing autoload dependency path. */
								__( 'Missing Composer dependency file: %s', 'jardin-toasts' ),
								$jt_required_file
							)
						);
					}
				}
			}
		}
		require_once $jt_autoload;
		define( 'JT_PLUGIN_RUNTIME_LOADED', true );
	} catch ( \Throwable $jt_load_error ) {
		define( 'JT_PLUGIN_RUNTIME_LOADED', false );
		add_action(
			'admin_notices',
			static function () use ( $jt_load_error ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				echo '<div class="notice notice-error"><p>';
				echo esc_html__( 'Jardin Toasts could not load its PHP dependencies.', 'jardin-toasts' );
				echo ' ';
				echo esc_html( $jt_load_error->getMessage() );
				echo '</p></div>';
			}
		);
	}
} else {
	define( 'JT_PLUGIN_RUNTIME_LOADED', false );
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

if ( jt_runtime_ready() ) {
	register_activation_hook( __FILE__, array( 'JT_Activator', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'JT_Deactivator', 'deactivate' ) );
}


/**
 * Load plugin text domain.
 *
 * @return void
 */
function jt_load_textdomain() {
	load_plugin_textdomain(
		'jardin-toasts',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'jt_load_textdomain' );

/**
 * Bootstrap plugin.
 *
 * @return void
 */
function jt_init_plugin() {
	if ( ! jt_runtime_ready() ) {
		return;
	}
	JT_Storage_Migration::maybe_migrate();
	JT_Storage_Migration::maybe_migrate_jb_prefix_storage_to_jt();
	JT_Storage_Migration::maybe_migrate_product_rename();
	JT_Plugin::instance()->init();
}
add_action( 'plugins_loaded', 'jt_init_plugin', 1 );

/**
 * Register plugin list action and meta links.
 *
 * @return void
 */
function jt_register_plugin_list_hooks() {
	if ( ! jt_runtime_ready() ) {
		return;
	}
	add_filter( 'plugin_action_links_' . plugin_basename( JT_PLUGIN_FILE ), 'jt_plugin_action_links' );
	add_filter( 'plugin_row_meta', 'jt_plugin_row_meta', 10, 2 );
}
add_action( 'admin_init', 'jt_register_plugin_list_hooks' );

/**
 * Add Settings link to the plugin action row.
 *
 * @param array $links Existing action links.
 * @return array Modified action links.
 */
function jt_plugin_action_links( $links ) {
	if ( ! class_exists( 'JT_Admin', false ) ) {
		return $links;
	}
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( JT_Admin::get_settings_url() ),
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
function jt_plugin_row_meta( $plugin_meta, $plugin_file ) {
	if ( plugin_basename( JT_PLUGIN_FILE ) !== $plugin_file ) {
		return $plugin_meta;
	}

	$new_links = array(
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( JT_GITHUB_URL ),
			esc_html__( 'GitHub', 'jardin-toasts' )
		),
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( JT_KOFI_URL ),
			esc_html__( 'Donate', 'jardin-toasts' )
		),
	);

	return array_merge( $plugin_meta, $new_links );
}
