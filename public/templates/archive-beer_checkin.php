<?php
/**
 * Archive template for beer check-ins.
 *
 * @package BeerJournal
 */

get_header();

$mf = get_option( 'bj_microformats_enabled', true ) ? 'h-feed' : '';
?>
<div id="primary" class="content-area bj-archive <?php echo $mf ? esc_attr( $mf ) : ''; ?>">
	<main id="main" class="site-main">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Beer check-ins', 'beer-journal' ); ?></h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="bj-checkin-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$part = BJ_PLUGIN_DIR . 'public/partials/checkin-card.php';
					if ( file_exists( $part ) ) {
						include $part;
					}
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No check-ins yet.', 'beer-journal' ); ?></p>
		<?php endif; ?>
	</main>
</div>
<?php
get_footer();
