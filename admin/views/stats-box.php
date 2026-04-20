<?php
/**
 * At-a-glance stats (dashboard strip).
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats   = bj_get_global_stats();
$last    = get_option( 'bj_last_rss_sync_at', '' );
$queue   = bj_get_rss_sync_queue();
$pending = is_array( $queue ) ? count( $queue ) : 0;
$draft_i = bj_count_draft_incomplete_checkins();
?>
<div class="bj-stats-strip" role="region" aria-label="<?php esc_attr_e( 'Beer Journal summary', 'beer-journal' ); ?>">
	<div class="bj-stats-strip__item">
		<span class="bj-stats-strip__value"><?php echo esc_html( number_format_i18n( $stats['publish'] ) ); ?></span>
		<span class="bj-stats-strip__label"><?php esc_html_e( 'Published', 'beer-journal' ); ?></span>
	</div>
	<div class="bj-stats-strip__item">
		<span class="bj-stats-strip__value"><?php echo esc_html( number_format_i18n( $stats['draft'] ) ); ?></span>
		<span class="bj-stats-strip__label"><?php esc_html_e( 'Drafts', 'beer-journal' ); ?></span>
	</div>
	<div class="bj-stats-strip__item bj-stats-strip__item--wide">
		<span class="bj-stats-strip__value bj-stats-strip__value--small">
			<?php
			if ( is_string( $last ) && '' !== $last ) {
				echo esc_html(
					wp_date(
						get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
						strtotime( $last )
					)
				);
			} else {
				echo '—';
			}
			?>
		</span>
		<span class="bj-stats-strip__label"><?php esc_html_e( 'Last RSS sync', 'beer-journal' ); ?></span>
	</div>
	<div class="bj-stats-strip__item">
		<span class="bj-stats-strip__value"><?php echo esc_html( number_format_i18n( $pending ) ); ?></span>
		<span class="bj-stats-strip__label"><?php esc_html_e( 'RSS queue', 'beer-journal' ); ?></span>
	</div>
	<div class="bj-stats-strip__item">
		<span class="bj-stats-strip__value"><?php echo esc_html( number_format_i18n( $draft_i ) ); ?></span>
		<span class="bj-stats-strip__label"><?php esc_html_e( 'Incomplete drafts', 'beer-journal' ); ?></span>
	</div>
</div>
