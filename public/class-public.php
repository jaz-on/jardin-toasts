<?php
/**
 * Front-end: templates, assets, schema.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JT_Public
 */
class JT_Public {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		require_once JT_PLUGIN_DIR . 'public/template-tags.php';

		add_filter( 'template_include', array( $this, 'template_include' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_head', array( $this, 'output_json_ld' ), 20 );
		add_filter( 'body_class', array( $this, 'body_class_layout' ) );
	}

	/**
	 * Add layout class for beer journal archives and taxonomies.
	 *
	 * @param array<int, string> $classes Body classes.
	 * @return array<int, string>
	 */
	public function body_class_layout( $classes ) {
		if ( is_post_type_archive( JT_Post_Type::POST_TYPE )
			|| is_tax( array( JT_Taxonomies::STYLE, JT_Taxonomies::BREWERY, JT_Taxonomies::VENUE ) ) ) {
			$classes[] = 'jt-archive-layout-' . jt_get_archive_layout();
		}
		return $classes;
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

		if ( is_post_type_archive( JT_Post_Type::POST_TYPE ) ) {
			$theme = locate_template(
				array(
					'jardin-toasts/archive-checkin.php',
					'jardin-toasts/archive-beer_checkin.php',
				)
			);
			if ( $theme ) {
				return $theme;
			}
			$path = JT_PLUGIN_DIR . 'public/templates/archive-checkin.php';
			return file_exists( $path ) ? $path : $template;
		}

		if ( is_singular( JT_Post_Type::POST_TYPE ) ) {
			$theme = locate_template(
				array(
					'jardin-toasts/single-checkin.php',
					'jardin-toasts/single-beer_checkin.php',
				)
			);
			if ( $theme ) {
				return $theme;
			}
			$path = JT_PLUGIN_DIR . 'public/templates/single-checkin.php';
			return file_exists( $path ) ? $path : $template;
		}

		if ( is_tax( JT_Taxonomies::STYLE ) || is_tax( JT_Taxonomies::BREWERY ) || is_tax( JT_Taxonomies::VENUE ) ) {
			$tax = get_queried_object();
			if ( $tax && isset( $tax->taxonomy ) ) {
				$theme = locate_template(
					array(
						'jardin-toasts/taxonomy-' . $tax->taxonomy . '.php',
						'jardin-beer/taxonomy-' . $tax->taxonomy . '.php',
					)
				);
				if ( $theme ) {
					return $theme;
				}
				$path = JT_PLUGIN_DIR . 'public/templates/taxonomy-' . $tax->taxonomy . '.php';
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
		if ( ! is_post_type_archive( JT_Post_Type::POST_TYPE )
			&& ! is_singular( JT_Post_Type::POST_TYPE )
			&& ! is_tax( array( JT_Taxonomies::STYLE, JT_Taxonomies::BREWERY, JT_Taxonomies::VENUE ) ) ) {
			return;
		}

		wp_enqueue_style(
			'jardin-toasts-public',
			JT_PLUGIN_URL . 'public/assets/css/public.css',
			array(),
			JT_VERSION
		);
	}

	/**
	 * Output Review JSON-LD for singular check-ins.
	 *
	 * @return void
	 */
	public function output_json_ld() {
		if ( ! is_singular( JT_Post_Type::POST_TYPE ) ) {
			return;
		}
		if ( ! get_option( 'jt_schema_enabled', true ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		$rating    = jt_get_checkin_rating_raw( $post_id );
		$beer_name = get_post_meta( $post_id, '_jardin_toasts_beer_name', true );
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

		$data = apply_filters( 'jardin_toasts_schema_review_data', apply_filters( 'jt_schema_review_data', $data, $post_id ), $post_id );

		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
