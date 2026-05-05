<?php
/**
 * Download Untappd images into the Media Library.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Jardin_Toasts_Image_Handler
 */
class Jardin_Toasts_Image_Handler {

	/**
	 * Sideload remote image as featured image for a post.
	 *
	 * @param string $url Image URL.
	 * @param int    $post_id Post ID.
	 * @param string $title Attachment title.
	 * @return int|WP_Error Attachment ID or error.
	 *
	 * When sideload fails and placeholders are enabled, uses the saved attachment ID, then the
	 * `jardin_toasts_placeholder_attachment_id` / `jardin_toasts_placeholder_attachment_id` filters so extensions can return another attachment (e.g. from an API).
	 */
	public function import_for_post( $url, $post_id, $title = '' ) {
		if ( ! get_option( 'jardin_toasts_import_images', true ) ) {
			return new WP_Error( 'disabled', __( 'Image import is disabled.', 'jardin-toasts' ) );
		}
		$url = esc_url_raw( $url );
		if ( ! $url ) {
			return new WP_Error( 'no_url', __( 'No image URL.', 'jardin-toasts' ) );
		}

		$hash = md5( $url );
		$existing = $this->find_attachment_by_source_hash( $hash, $url );
		if ( $existing ) {
			set_post_thumbnail( $post_id, $existing );
			return $existing;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$att_id = media_sideload_image( $url, $post_id, $title, 'id' );
		if ( is_wp_error( $att_id ) ) {
			if ( ! Jardin_Toasts_Settings::get( 'jardin_toasts_use_placeholder_image' ) ) {
				return $att_id;
			}
			$placeholder = absint( Jardin_Toasts_Settings::get( 'jardin_toasts_placeholder_image_id' ) );
			$placeholder = (int) apply_filters(
				'jardin_toasts_placeholder_attachment_id',
				(int) apply_filters( 'jardin_toasts_placeholder_attachment_id', $placeholder, $post_id, $url, (string) $title ),
				$post_id,
				$url,
				(string) $title
			);
			if ( $placeholder && wp_attachment_is_image( $placeholder ) ) {
				set_post_thumbnail( $post_id, $placeholder );
				Jardin_Toasts_Logger::info( 'Image sideload failed; using placeholder attachment ' . (string) $placeholder );
				return $placeholder;
			}
			return $att_id;
		}

		update_post_meta( $att_id, '_jardin_toasts_image_hash', $hash );
		update_post_meta( $att_id, '_jardin_toasts_image_source_url', $url );
		set_post_thumbnail( $post_id, $att_id );

		return (int) $att_id;
	}

	/**
	 * Find existing attachment by hash meta.
	 *
	 * @param string $hash MD5 of URL.
	 * @param string $url Source URL.
	 * @return int Attachment ID or 0.
	 */
	private function find_attachment_by_source_hash( $hash, $url ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
				'_jardin_toasts_image_hash',
				$hash
			)
		);
		if ( $id ) {
			return absint( $id );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
				'_jardin_toasts_image_source_url',
				$url
			)
		);
		return $id ? absint( $id ) : 0;
	}
}
