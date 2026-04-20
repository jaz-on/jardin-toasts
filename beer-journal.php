<?php
/**
 * Plugin Name:       Beer Journal for Untappd
 * Plugin URI:        https://github.com/jaz-on/beer-journal
 * Description:       Syncs and displays your Untappd beer check-ins on WordPress with templates and media handling.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Jason Rouet
 * Author URI:        https://jasonrouet.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       beer-journal
 * GitHub Plugin URI: https://github.com/jaz-on/beer-journal
 * Primary Branch:    dev
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BJ_VERSION', '1.0.0' );
define( 'BJ_PLUGIN_FILE', __FILE__ );
define( 'BJ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BJ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BJ_GITHUB_URL', 'https://github.com/jaz-on/beer-journal' );
define( 'BJ_KOFI_URL', 'https://ko-fi.com/jasonrouet' );

$bj_autoload = BJ_PLUGIN_DIR . 'vendor/autoload.php';
if ( is_readable( $bj_autoload ) ) {
	require_once $bj_autoload;

	register_activation_hook( __FILE__, array( 'BJ_Activator', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'BJ_Deactivator', 'deactivate' ) );
} else {
	add_action(
		'admin_notices',
		function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Beer Journal requires Composer dependencies. Run composer install in the plugin directory.', 'beer-journal' );
			echo '</p></div>';
		}
	);
}


/**
 * Load plugin text domain.
 *
 * @return void
 */
function bj_load_textdomain() {
	load_plugin_textdomain(
		'beer-journal',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'bj_load_textdomain' );

/**
 * Bootstrap plugin.
 *
 * @return void
 */
function bj_init_plugin() {
	if ( ! is_readable( BJ_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		return;
	}
	BJ_Plugin::instance()->init();
}
add_action( 'plugins_loaded', 'bj_init_plugin', 1 );

/**
 * Register plugin list action and meta links.
 *
 * @return void
 */
function bj_register_plugin_list_hooks() {
	add_filter( 'plugin_action_links_' . plugin_basename( BJ_PLUGIN_FILE ), 'bj_plugin_action_links' );
	add_filter( 'plugin_row_meta', 'bj_plugin_row_meta', 10, 2 );
}
add_action( 'admin_init', 'bj_register_plugin_list_hooks' );

/**
 * Add Settings link to the plugin action row.
 *
 * @param array $links Existing action links.
 * @return array Modified action links.
 */
function bj_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=beer-journal' ) ),
		esc_html__( 'Settings', 'beer-journal' )
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
function bj_plugin_row_meta( $plugin_meta, $plugin_file ) {
	if ( plugin_basename( BJ_PLUGIN_FILE ) !== $plugin_file ) {
		return $plugin_meta;
	}

	$new_links = array(
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( BJ_GITHUB_URL ),
			esc_html__( 'GitHub', 'beer-journal' )
		),
		sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( BJ_KOFI_URL ),
			esc_html__( 'Donate', 'beer-journal' )
		),
	);

	return array_merge( $plugin_meta, $new_links );
}
