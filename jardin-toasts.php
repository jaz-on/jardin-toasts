<?php
/**
 * Plugin Name:       Jardin Toasts for Untappd
 * Plugin URI:        https://github.com/jaz-on/jardin-toasts
 * Description:       Syncs and displays your Untappd beer check-ins on WordPress with templates and media handling.
 * Version:           1.0.0
 * Requires at least: 6.0
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

define( 'JB_VERSION', '1.0.0' );
define( 'JB_PLUGIN_FILE', __FILE__ );
define( 'JB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JB_GITHUB_URL', 'https://github.com/jaz-on/jardin-toasts' );
define( 'JB_KOFI_URL', 'https://ko-fi.com/jasonrouet' );

// Load before Composer so a stale classmap (paths to removed `* 2.php` files) cannot fatal the site.
foreach ( array(
	JB_PLUGIN_DIR . 'includes/class-taxonomies.php',
	JB_PLUGIN_DIR . 'includes/class-logger.php',
	JB_PLUGIN_DIR . 'includes/class-meta-fields.php',
	JB_PLUGIN_DIR . 'public/class-public.php',
) as $jb_bootstrap_file ) {
	if ( is_readable( $jb_bootstrap_file ) ) {
		require_once $jb_bootstrap_file;
	}
}

$jb_autoload = JB_PLUGIN_DIR . 'vendor/autoload.php';
if ( is_readable( $jb_autoload ) ) {
	require_once $jb_autoload;

	register_activation_hook( __FILE__, array( 'JB_Activator', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'JB_Deactivator', 'deactivate' ) );
} else {
	add_action(
		'admin_notices',
		function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Jardin Toasts requires Composer dependencies. Run composer install in the plugin directory.', 'jardin-toasts' );
			echo '</p></div>';
		}
	);
}


/**
 * Load plugin text domain.
 *
 * @return void
 */
function jb_load_textdomain() {
	load_plugin_textdomain(
		'jardin-toasts',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'jb_load_textdomain' );

/**
 * Bootstrap plugin.
 *
 * @return void
 */
function jb_init_plugin() {
	if ( ! is_readable( JB_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		return;
	}
	JB_Storage_Migration::maybe_migrate();
	JB_Storage_Migration::maybe_migrate_product_rename();
	JB_Plugin::instance()->init();
}
add_action( 'plugins_loaded', 'jb_init_plugin', 1 );

/**
 * Register plugin list action and meta links.
 *
 * @return void
 */
function jb_register_plugin_list_hooks() {
	add_filter( 'plugin_action_links_' . plugin_basename( JB_PLUGIN_FILE ), 'jb_plugin_action_links' );
	add_filter( 'plugin_row_meta', 'jb_plugin_row_meta', 10, 2 );
}
add_action( 'admin_init', 'jb_register_plugin_list_hooks' );

/**
 * Add Settings link to the plugin action row.
 *
 * @param array $links Existing action links.
 * @return array Modified action links.
 */
function jb_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( JB_Admin::get_settings_url() ),
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
function jb_plugin_row_meta( $plugin_meta, $plugin_file ) {
	if ( plugin_basename( JB_PLUGIN_FILE ) !== $plugin_file ) {
		return $plugin_meta;
	}

	$new_links = array(
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( JB_GITHUB_URL ),
			esc_html__( 'GitHub', 'jardin-toasts' )
		),
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( JB_KOFI_URL ),
			esc_html__( 'Donate', 'jardin-toasts' )
		),
	);

	return array_merge( $plugin_meta, $new_links );
}
