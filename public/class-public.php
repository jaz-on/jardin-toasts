<?php
/**
 * Front-end: templates, assets, schema.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Public
 */
class BJ_Public {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		require_once BJ_PLUGIN_DIR . 'public/template-tags.php';

		add_filter( 'template_include', array( $this, 'template_include' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_head', array( $this, 'output_json_ld' ), 20 );
	}

	/**
	 * Load plugin templates when theme has no override.
	 *
	 * @param string $template Path.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( is_embed() ) {
			return $template;
		}

		if ( is_post_type_archive( BJ_Post_Type::POST_TYPE ) ) {
			$theme = locate_template( array( 'beer-journal/archive-beer_checkin.php' ) );
			if ( $theme ) {
				return $theme;
			}
			$path = BJ_PLUGIN_DIR . 'public/templates/archive-beer_checkin.php';
			return file_exists( $path ) ? $path : $template;
		}

		if ( is_singular( BJ_Post_Type::POST_TYPE ) ) {
			$theme = locate_template( array( 'beer-journal/single-beer_checkin.php' ) );
			if ( $theme ) {
				return $theme;
			}
			$path = BJ_PLUGIN_DIR . 'public/templates/single-beer_checkin.php';
			return file_exists( $path ) ? $path : $template;
		}

		if ( is_tax( BJ_Taxonomies::STYLE ) || is_tax( BJ_Taxonomies::BREWERY ) || is_tax( BJ_Taxonomies::VENUE ) ) {
			$tax = get_queried_object();
			if ( $tax && isset( $tax->taxonomy ) ) {
				$theme = locate_template( array( 'beer-journal/taxonomy-' . $tax->taxonomy . '.php' ) );
				if ( $theme ) {
					return $theme;
				}
				$path = BJ_PLUGIN_DIR . 'public/templates/taxonomy-' . $tax->taxonomy . '.php';
				if ( file_exists( $path ) ) {
					return $path;
				}
			}
		}

		return $template;
	}

	/**
	 * Front styles.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_post_type_archive( BJ_Post_Type::POST_TYPE )
			&& ! is_singular( BJ_Post_Type::POST_TYPE )
			&& ! is_tax( array( BJ_Taxonomies::STYLE, BJ_Taxonomies::BREWERY, BJ_Taxonomies::VENUE ) ) ) {
			return;
		}

		wp_enqueue_style(
			'beer-journal-public',
			BJ_PLUGIN_URL . 'public/assets/css/public.css',
			array(),
			BJ_VERSION
		);
	}

	/**
	 * Output Review JSON-LD for singular check-ins.
	 *
	 * @return void
	 */
	public function output_json_ld() {
		if ( ! is_singular( BJ_Post_Type::POST_TYPE ) ) {
			return;
		}
		if ( ! get_option( 'bj_schema_enabled', true ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		$rating    = bj_get_checkin_rating_raw( $post_id );
		$beer_name = get_post_meta( $post_id, '_bj_beer_name', true );
		if ( ! is_string( $beer_name ) || '' === $beer_name ) {
			$beer_name = get_the_title( $post_id );
		}

		$data = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Review',
			'itemReviewed' => array(
				'@type' => 'Product',
				'name'  => $beer_name,
			),
			'reviewBody' => wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ),
			'datePublished' => get_the_date( 'c', $post_id ),
		);

		if ( null !== $rating ) {
			$data['reviewRating'] = array(
				'@type'       => 'Rating',
				'ratingValue' => $rating,
				'bestRating'  => 5,
				'worstRating' => 0,
			);
		}

		$data = apply_filters( 'bj_schema_review_data', $data, $post_id );

		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
