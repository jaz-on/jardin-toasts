<?php
/**
 * jardin-toasts/styles-breakdown — top beer styles as a bar chart.
 *
 * @package JardinToasts
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

$post_type = Jardin_Toasts_Post_Type::POST_TYPE;
$tax_style = Jardin_Toasts_Taxonomies::STYLE;
$top_n     = isset( $attributes['topCount'] ) ? max( 3, min( 15, absint( $attributes['topCount'] ) ) ) : 7;

$rows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT t.name AS name, COUNT(p.ID) AS cnt
		   FROM {$wpdb->terms} t
		   INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
		   INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		   INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		  WHERE tt.taxonomy = %s
		    AND p.post_type = %s
		    AND p.post_status = 'publish'
		  GROUP BY t.term_id
		  ORDER BY cnt DESC",
		$tax_style,
		$post_type
	)
); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

if ( empty( $rows ) ) {
	return '<div class="jardin-toasts-block-placeholder jardin-toasts-block-placeholder--empty"><p>' . esc_html__( 'No beer styles to chart yet.', 'jardin-toasts' ) . '</p></div>';
}

$top_rows  = array_slice( $rows, 0, $top_n );
$rest_rows = array_slice( $rows, $top_n );
$rest_sum  = 0;
foreach ( $rest_rows as $r ) {
	$rest_sum += (int) $r->cnt;
}

$max_count = 0;
foreach ( $top_rows as $r ) {
	if ( (int) $r->cnt > $max_count ) {
		$max_count = (int) $r->cnt;
	}
}
if ( $rest_sum > $max_count ) {
	$max_count = $rest_sum;
}
if ( $max_count <= 0 ) {
	$max_count = 1;
}

ob_start();
?>
<ul class="jardin-toasts-styles-breakdown wp-block-jardin-toasts-styles-breakdown" role="list">
	<?php foreach ( $top_rows as $row ) :
		$name = (string) $row->name;
		$cnt  = (int) $row->cnt;
		$pct  = (int) round( ( $cnt / $max_count ) * 100 );
		?>
		<li class="jardin-toasts-styles-breakdown__row">
			<span class="jardin-toasts-styles-breakdown__name"><?php echo esc_html( $name ); ?></span>
			<span class="jardin-toasts-styles-breakdown__bar" aria-hidden="true"><span style="width: <?php echo esc_attr( (string) $pct ); ?>%"></span></span>
			<span class="jardin-toasts-styles-breakdown__count"><?php echo esc_html( number_format_i18n( $cnt ) ); ?></span>
		</li>
	<?php endforeach; ?>
	<?php if ( $rest_sum > 0 ) :
		$pct = (int) round( ( $rest_sum / $max_count ) * 100 );
		?>
		<li class="jardin-toasts-styles-breakdown__row jardin-toasts-styles-breakdown__row--rest">
			<span class="jardin-toasts-styles-breakdown__name"><?php esc_html_e( 'Others', 'jardin-toasts' ); ?></span>
			<span class="jardin-toasts-styles-breakdown__bar" aria-hidden="true"><span style="width: <?php echo esc_attr( (string) $pct ); ?>%"></span></span>
			<span class="jardin-toasts-styles-breakdown__count"><?php echo esc_html( number_format_i18n( $rest_sum ) ); ?></span>
		</li>
	<?php endif; ?>
</ul>
<?php
return (string) ob_get_clean();
