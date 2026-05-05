<?php
/**
 * Archive template for beer check-ins.
 *
 * @package JardinToasts
 */

get_header();

$mf = get_option( 'jt_microformats_enabled', true ) ? 'h-feed' : '';
?>
<div id="primary" class="content-area jt-archive <?php echo $mf ? esc_attr( $mf ) : ''; ?>">
	<main id="main" class="site-main">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Beer check-ins', 'jardin-toasts' ); ?></h1>
		</header>
		<?php
		$jt_empty_message = __( 'No check-ins yet.', 'jardin-toasts' );
		include JT_PLUGIN_DIR . 'public/partials/archive-loop.php';
		?>
	</main>
</div>
<?php
get_footer();
