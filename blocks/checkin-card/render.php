<?php
/**
 * @package BeerJournal
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $attributes['postId'] ) ? absint( $attributes['postId'] ) : 0;
if ( $post_id <= 0 && is_singular( BJ_Post_Type::POST_TYPE ) ) {
	$post_id = get_the_ID();
}
if ( $post_id <= 0 || BJ_Post_Type::POST_TYPE !== get_post_type( $post_id ) ) {
	return '<p class="bj-block-placeholder">' . esc_html__( 'Select a check-in or view on a single check-in page.', 'beer-journal' ) . '</p>';
}

$post = get_post( $post_id );
if ( ! $post ) {
	return '';
}

ob_start();
$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
setup_postdata( $post );
$partial = BJ_PLUGIN_DIR . 'public/partials/checkin-card.php';
if ( is_readable( $partial ) ) {
	include $partial;
}
wp_reset_postdata();
return (string) ob_get_clean();
