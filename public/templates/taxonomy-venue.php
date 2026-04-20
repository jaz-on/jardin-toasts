<?php
/**
 * Taxonomy archive: venue.
 *
 * @package BeerJournal
 */

get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<header class="page-header">
			<h1 class="page-title"><?php single_term_title(); ?></h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="bj-checkin-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					include BJ_PLUGIN_DIR . 'public/partials/checkin-card.php';
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No check-ins found.', 'beer-journal' ); ?></p>
		<?php endif; ?>
	</main>
</div>
<?php
get_footer();
