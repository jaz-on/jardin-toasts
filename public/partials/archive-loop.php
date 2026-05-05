<?php
/**
 * Shared archive loop: grid or table (option jardin_toasts_archive_layout).
 *
 * Expects $jardin_toasts_empty_message (string) in scope when no posts.
 *
 * @package JardinToasts
 */

if ( ! have_posts() ) {
	$msg = isset( $jardin_toasts_empty_message ) && is_string( $jardin_toasts_empty_message )
		? $jardin_toasts_empty_message
		: __( 'No check-ins found.', 'jardin-toasts' );
	echo '<p>' . esc_html( $msg ) . '</p>';
	return;
}

$layout = jardin_toasts_get_archive_layout();

if ( 'table' === $layout ) :
	?>
	<table class="jardin-toasts-checkin-table">
		<thead>
			<tr>
				<th class="jardin-toasts-col-thumb"><?php esc_html_e( 'Photo', 'jardin-toasts' ); ?></th>
				<th class="jardin-toasts-col-title"><?php esc_html_e( 'Check-in', 'jardin-toasts' ); ?></th>
				<th class="jardin-toasts-col-rating"><?php esc_html_e( 'Rating', 'jardin-toasts' ); ?></th>
				<th class="jardin-toasts-col-date"><?php esc_html_e( 'Date', 'jardin-toasts' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			while ( have_posts() ) :
				the_post();
				include JARDIN_TOASTS_PLUGIN_DIR . 'public/partials/checkin-row.php';
			endwhile;
			?>
		</tbody>
	</table>
	<?php
else :
	?>
	<div class="jardin-toasts-checkin-grid">
		<?php
		while ( have_posts() ) :
			the_post();
			include JARDIN_TOASTS_PLUGIN_DIR . 'public/partials/checkin-card.php';
		endwhile;
		?>
	</div>
	<?php
endif;

the_posts_pagination();
