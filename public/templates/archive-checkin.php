<?php
/**
 * Archive template for beer check-ins.
 *
 * @package JardinToasts
 */

get_header();

$mf = get_option( 'jardin_toasts_microformats_enabled', true ) ? 'h-feed' : '';
?>
<div id="primary" class="content-area jardin-toasts-archive <?php echo $mf ? esc_attr( $mf ) : ''; ?>">
	<main id="main" class="site-main">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Beer check-ins', 'jardin-toasts' ); ?></h1>
		</header>
		<?php
		$jardin_toasts_empty_message = __( 'No check-ins yet.', 'jardin-toasts' );
		include JARDIN_TOASTS_PLUGIN_DIR . 'public/partials/archive-loop.php';
		?>
	</main>
</div>
<?php
get_footer();
