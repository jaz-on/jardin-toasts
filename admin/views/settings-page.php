<?php
/**
 * Tabbed settings page markup.
 *
 * @package JardinToasts
 *
 * @var string $tab Active tab slug (set by Jardin_Toasts_Admin::render_settings_page()).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = Jardin_Toasts_Admin::get_settings_url();
$tabs     = array(
	'untappd'  => __( 'Untappd account', 'jardin-toasts' ),
	'sync'     => __( 'Import & sync', 'jardin-toasts' ),
	'display'  => __( 'Display & content', 'jardin-toasts' ),
	'advanced' => __( 'Advanced', 'jardin-toasts' ),
);

$tab_intros = array(
	'untappd'  => __( 'Your RSS URL and username identify the same Untappd account. RSS brings in recent check-ins; a CSV data export (privacy request or Insider archive) is used for full history and complete fields.', 'jardin-toasts' ),
	'sync'     => __( 'Scheduled sync reads the public RSS feed. For your complete journal, import the check-ins CSV from Untappd’s data export.', 'jardin-toasts' ),
	'display'  => __( 'Control how archives look, what gets stored with each check-in, and how ratings map to stars on the front of your site.', 'jardin-toasts' ),
	'advanced' => __( 'Fine-tune RSS import limits, structured data, notifications, and troubleshooting logs.', 'jardin-toasts' ),
);

$rss_username = jardin_toasts_parse_username_from_rss_url( jardin_toasts_get_rss_feed_url() );
?>
<div class="wrap jardin-toasts-admin-wrap">
	<div class="jardin-toasts-settings-hero">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p class="jardin-toasts-settings-hero__lead"><?php esc_html_e( 'Untappd → WordPress: sync, import, and present your beer journal with theme-friendly templates.', 'jardin-toasts' ); ?></p>
	</div>

	<nav class="nav-tab-wrapper jardin-toasts-nav-tabs" aria-label="<?php esc_attr_e( 'Jardin Toasts settings sections', 'jardin-toasts' ); ?>">
		<?php foreach ( $tabs as $id => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $id, $base_url ) ); ?>" class="nav-tab <?php echo $tab === $id ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="options.php" class="jardin-toasts-settings-form">
		<?php settings_fields( Jardin_Toasts_Settings::OPTION_GROUP ); ?>

		<div class="jardin-toasts-tab-panel">
			<p class="jardin-toasts-tab-intro"><?php echo isset( $tab_intros[ $tab ] ) ? esc_html( $tab_intros[ $tab ] ) : ''; ?></p>

			<?php
			/*
			 * Every tab body lives in admin/views/settings/ so options.php still receives all keys on save.
			 */
			require JARDIN_TOASTS_PLUGIN_DIR . 'admin/views/settings/tab-untappd.php';
			require JARDIN_TOASTS_PLUGIN_DIR . 'admin/views/settings/tab-sync.php';
			require JARDIN_TOASTS_PLUGIN_DIR . 'admin/views/settings/tab-display.php';
			require JARDIN_TOASTS_PLUGIN_DIR . 'admin/views/settings/tab-advanced.php';
			?>
		</div>

		<?php if ( 'sync' !== $tab ) : ?>
		<div class="jardin-toasts-settings-footer">
			<?php submit_button( __( 'Save changes', 'jardin-toasts' ), 'primary large', 'submit', false ); ?>
		</div>
		<?php endif; ?>
	</form>
	<?php if ( isset( $_GET['tab'] ) && 'rating' === sanitize_key( wp_unslash( $_GET['tab'] ) ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
	<script>
	(function () {
		var el = document.getElementById( 'jardin-toasts-ratings-section' );
		if ( el ) {
			el.scrollIntoView( { block: 'start' } );
		}
	})();
	</script>
	<?php endif; ?>
</div>
