<?php
/**
 * Template tags for themes.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Raw Untappd rating for a check-in post.
 *
 * @param int|null $post_id Post ID.
 * @return float|null
 */
function jt_get_checkin_rating_raw( $post_id = null ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return null;
	}
	$v = get_post_meta( $post_id, '_jardin_toasts_rating_raw', true );
	return '' === $v || null === $v ? null : floatval( $v );
}

/**
 * Rounded star level (0–5).
 *
 * @param int|null $post_id Post ID.
 * @return int|null
 */
function jt_get_checkin_rating_rounded( $post_id = null ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return null;
	}
	$v = get_post_meta( $post_id, '_jardin_toasts_rating_rounded', true );
	return '' === $v || null === $v ? null : absint( $v );
}

/**
 * Echo or return star markup for rounded rating.
 *
 * @param int|null $post_id Post ID.
 * @param bool     $echo Echo.
 * @return string|void
 */
function jt_the_rating_stars( $post_id = null, $echo = true ) {
	$r = jt_get_checkin_rating_rounded( $post_id );
	if ( null === $r ) {
		return '';
	}
	$out = '<span class="jt-stars" aria-label="' . esc_attr( sprintf( /* translators: %d: stars */ __( '%d out of 5 stars', 'jardin-toasts' ), $r ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= $i <= $r ? '★' : '☆';
	}
	$out .= '</span>';
	$out = apply_filters( 'jardin_toasts_rating_display', apply_filters( 'jt_rating_display', $out, $post_id, $r ), $post_id, $r );
	if ( $echo ) {
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $out;
	}
}
