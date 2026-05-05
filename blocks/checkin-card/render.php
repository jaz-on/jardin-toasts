<?php
/**
 * @package JardinToasts
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $attributes['postId'] ) ? absint( $attributes['postId'] ) : 0;
if ( $post_id <= 0 && is_singular( Jardin_Toasts_Post_Type::POST_TYPE ) ) {
	$post_id = get_the_ID();
}
if ( $post_id <= 0 || Jardin_Toasts_Post_Type::POST_TYPE !== get_post_type( $post_id ) ) {
	return '<p class="jardin-toasts-block-placeholder">' . esc_html__( 'Select a check-in or view on a single check-in page.', 'jardin-toasts' ) . '</p>';
}

$post = get_post( $post_id );
if ( ! $post ) {
	return '';
}

ob_start();
$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
setup_postdata( $post );
$partial = JARDIN_TOASTS_PLUGIN_DIR . 'public/partials/checkin-card.php';
if ( is_readable( $partial ) ) {
	include $partial;
}
wp_reset_postdata();
return (string) ob_get_clean();
