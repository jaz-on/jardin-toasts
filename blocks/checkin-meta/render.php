<?php
/**
 * jardin-toasts/checkin-meta — beer-card meta (brewery/style/ABV/IBU/rating) + Untappd link.
 *
 * @package JardinToasts
 *
 * @var array         $attributes Block attributes.
 * @var string        $content    Block default content.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$pid = 0;
if ( isset( $block ) && $block instanceof WP_Block && ! empty( $block->context['postId'] ) ) {
	$pid = (int) $block->context['postId'];
}
if ( $pid <= 0 && is_singular( Jardin_Toasts_Post_Type::POST_TYPE ) ) {
	$pid = (int) get_the_ID();
}
if ( $pid <= 0 || Jardin_Toasts_Post_Type::POST_TYPE !== get_post_type( $pid ) ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return '<p class="jardin-toasts-block-placeholder">' . esc_html__( 'Beer-card meta appears here on a single check-in (with rating, brewery, style, ABV, IBU).', 'jardin-toasts' ) . '</p>';
	}
	return '';
}

$brewery = (string) get_post_meta( $pid, '_jardin_toasts_brewery_name', true );
$style   = (string) get_post_meta( $pid, '_jardin_toasts_beer_style', true );
$abv     = get_post_meta( $pid, '_jardin_toasts_beer_abv', true );
$ibu     = get_post_meta( $pid, '_jardin_toasts_beer_ibu', true );
$url     = (string) get_post_meta( $pid, '_jardin_toasts_checkin_url', true );
$rounded = function_exists( 'jardin_toasts_get_checkin_rating_rounded' )
	? jardin_toasts_get_checkin_rating_rounded( $pid )
	: null;

$abv_str = '';
if ( '' !== $abv && null !== $abv ) {
	$abv_float = (float) $abv;
	if ( $abv_float > 0 ) {
		$abv_str = number_format_i18n( $abv_float, 1 ) . "\xc2\xa0%";
	}
}

$ibu_str = '';
if ( '' !== $ibu && null !== $ibu ) {
	$ibu_int = (int) $ibu;
	if ( $ibu_int > 0 ) {
		$ibu_str = (string) $ibu_int;
	}
}

$stats = array();
if ( '' !== $brewery ) {
	$stats[] = array(
		'label' => __( 'brewery', 'jardin-toasts' ),
		'value' => $brewery,
	);
}
if ( '' !== $style ) {
	$stats[] = array(
		'label' => __( 'style', 'jardin-toasts' ),
		'value' => $style,
	);
}
if ( '' !== $abv_str ) {
	$stats[] = array(
		'label' => __( 'abv', 'jardin-toasts' ),
		'value' => $abv_str,
	);
}
if ( '' !== $ibu_str ) {
	$stats[] = array(
		'label' => __( 'ibu', 'jardin-toasts' ),
		'value' => $ibu_str,
	);
}

$rating_html = '';
if ( null !== $rounded ) {
	$rating_html = function_exists( 'jardin_toasts_the_rating_stars' )
		? (string) jardin_toasts_the_rating_stars( $pid, false )
		: str_repeat( '★', (int) $rounded ) . str_repeat( '☆', max( 0, 5 - (int) $rounded ) );
}

$has_any = ! empty( $stats ) || '' !== $rating_html || '' !== $url;
if ( ! $has_any ) {
	return '';
}

ob_start();
?>
<div class="jardin-toasts-checkin-meta">
	<?php if ( ! empty( $stats ) || '' !== $rating_html ) : ?>
	<div class="beer-card-meta">
		<?php foreach ( $stats as $stat ) : ?>
		<div class="beer-stat">
			<span class="beer-stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
			<span class="beer-stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
		</div>
		<?php endforeach; ?>
		<?php if ( '' !== $rating_html ) : ?>
		<div class="beer-stat beer-stat-rating">
			<span class="beer-stat-label"><?php esc_html_e( 'rating', 'jardin-toasts' ); ?></span>
			<span class="beer-stat-value p-rating"><?php echo $rating_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ( '' !== $url ) : ?>
	<p class="jardin-toasts-untappd-link">
		<a class="u-url" href="<?php echo esc_url( $url ); ?>" rel="noopener noreferrer" target="_blank">
			<?php esc_html_e( 'View on Untappd', 'jardin-toasts' ); ?>
			<svg class="external-link-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
		</a>
	</p>
	<?php endif; ?>
</div>
<?php
return (string) ob_get_clean();
