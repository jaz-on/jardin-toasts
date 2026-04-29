<?php
/**
 * Shared archive loop: grid or table (option jb_archive_layout).
 *
 * Expects $jb_empty_message (string) in scope when no posts.
 *
 * @package JardinToasts
 */

if ( ! have_posts() ) {
	$msg = isset( $jb_empty_message ) && is_string( $jb_empty_message )
		? $jb_empty_message
		: __( 'No check-ins found.', 'jardin-toasts' );
	echo '<p>' . esc_html( $msg ) . '</p>';
	return;
}

$layout = jb_get_archive_layout();

if ( 'table' === $layout ) :
	?>
	<table class="jb-checkin-table">
		<thead>
			<tr>
				<th class="jb-col-thumb"><?php esc_html_e( 'Photo', 'jardin-toasts' ); ?></th>
				<th class="jb-col-title"><?php esc_html_e( 'Check-in', 'jardin-toasts' ); ?></th>
				<th class="jb-col-rating"><?php esc_html_e( 'Rating', 'jardin-toasts' ); ?></th>
				<th class="jb-col-date"><?php esc_html_e( 'Date', 'jardin-toasts' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			while ( have_posts() ) :
				the_post();
				include JB_PLUGIN_DIR . 'public/partials/checkin-row.php';
			endwhile;
			?>
		</tbody>
	</table>
	<?php
else :
	?>
	<div class="jb-checkin-grid">
		<?php
		while ( have_posts() ) :
			the_post();
			include JB_PLUGIN_DIR . 'public/partials/checkin-card.php';
		endwhile;
		?>
	</div>
	<?php
endif;

the_posts_pagination();
