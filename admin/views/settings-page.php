<?php
/**
 * Tabbed settings page markup.
 *
 * @package JardinToasts
 *
 * @var string $tab Active tab slug (set by JT_Admin::render_settings_page()).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = JT_Admin::get_settings_url();
$tabs     = array(
	'untappd'  => __( 'Untappd account', 'jardin-toasts' ),
	'sync'     => __( 'Import & sync', 'jardin-toasts' ),
	'display'  => __( 'Display & content', 'jardin-toasts' ),
	'advanced' => __( 'Advanced', 'jardin-toasts' ),
);

$tab_intros = array(
	'untappd'  => __( 'Your RSS URL and profile username identify the same Untappd account. RSS discovers new check-ins; the public profile is used for historical backfill and for scraping full details.', 'jardin-toasts' ),
	'sync'     => __( 'Background jobs pull recent check-ins from RSS and can drain the historical import queue. Use “Run sync now” for an immediate RSS run, discover / import batches for older check-ins, or import a GDPR data-export CSV for a full history.', 'jardin-toasts' ),
	'display'  => __( 'Control how archives look, what gets stored with each check-in, and how ratings map to stars on the front of your site.', 'jardin-toasts' ),
	'advanced' => __( 'Fine-tune scraping pace, structured data, notifications, and troubleshooting logs.', 'jardin-toasts' ),
);

$rss_username = jt_parse_username_from_rss_url( jt_get_rss_feed_url() );
extract( jt_settings_importer_choice_lists(), EXTR_SKIP );
?>
<div class="wrap jt-admin-wrap">
	<div class="jt-settings-hero">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p class="jt-settings-hero__lead"><?php esc_html_e( 'Untappd → WordPress: sync, import, and present your beer journal with theme-friendly templates.', 'jardin-toasts' ); ?></p>
	</div>

	<nav class="nav-tab-wrapper jt-nav-tabs" aria-label="<?php esc_attr_e( 'Jardin Toasts settings sections', 'jardin-toasts' ); ?>">
		<?php foreach ( $tabs as $id => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $id, $base_url ) ); ?>" class="nav-tab <?php echo $tab === $id ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="options.php" class="jt-settings-form">
		<?php settings_fields( JT_Settings::OPTION_GROUP ); ?>

		<div class="jt-tab-panel">
			<p class="jt-tab-intro"><?php echo isset( $tab_intros[ $tab ] ) ? esc_html( $tab_intros[ $tab ] ) : ''; ?></p>

			<?php
			/*
			 * Every tab body lives in admin/views/settings/ so options.php still receives all keys on save.
			 */
			require JT_PLUGIN_DIR . 'admin/views/settings/tab-untappd.php';
			require JT_PLUGIN_DIR . 'admin/views/settings/tab-sync.php';
			require JT_PLUGIN_DIR . 'admin/views/settings/tab-display.php';
			require JT_PLUGIN_DIR . 'admin/views/settings/tab-advanced.php';
			?>
		</div>

		<?php if ( 'sync' !== $tab ) : ?>
		<div class="jt-settings-footer">
			<?php submit_button( __( 'Save changes', 'jardin-toasts' ), 'primary large', 'submit', false ); ?>
		</div>
		<?php endif; ?>
	</form>
	<?php if ( isset( $_GET['tab'] ) && 'rating' === sanitize_key( wp_unslash( $_GET['tab'] ) ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
	<script>
	(function () {
		var el = document.getElementById( 'jt-ratings-section' );
		if ( el ) {
			el.scrollIntoView( { block: 'start' } );
		}
	})();
	</script>
	<?php endif; ?>
</div>
