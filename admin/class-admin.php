<?php
/**
 * Admin UI: settings, import, logs.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Jardin_Toasts_Admin
 */
class Jardin_Toasts_Admin {

	/**
	 * Slug for the tabbed settings page under the CPT (admin.php?page=…).
	 */
	public const SETTINGS_PAGE_SLUG = 'jardin-toasts-settings';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'maybe_redirect_legacy_settings_url' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_submenu' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_' . Jardin_Toasts_Keys::AJAX_SYNC_NOW, array( $this, 'ajax_sync_now' ) );
		add_action( 'wp_ajax_' . Jardin_Toasts_Keys::AJAX_TEST_RSS, array( $this, 'ajax_test_rss' ) );
		add_action( 'wp_ajax_' . Jardin_Toasts_Keys::AJAX_IMPORT_GDPR_CSV, array( $this, 'ajax_import_gdpr_csv' ) );
		add_action( 'wp_ajax_jt_sync_now', array( $this, 'ajax_sync_now' ) );
	}

	/**
	 * Redirect legacy admin.php?page=… bookmarks to the current settings URL.
	 *
	 * @return void
	 */
	public function maybe_redirect_legacy_settings_url() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$legacy_pages = array(
			'jardin-beer',
			'jardin-beer-settings',
			'jb_jardin_beer_settings',
			'jardin_toasts_jardin_beer_settings',
		);
		if ( ! in_array( $page, $legacy_pages, true ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$dest = self::get_settings_url();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		if ( $tab ) {
			$dest = add_query_arg( 'tab', $tab, $dest );
		}
		wp_safe_redirect( $dest );
		exit;
	}

	/**
	 * Settings as last submenu: Check-ins (list) → taxonomies → Settings.
	 *
	 * @return void
	 */
	public function register_settings_submenu() {
		add_submenu_page(
			Jardin_Toasts_Post_Type::ADMIN_MENU_SLUG,
			__( 'Jardin Toasts Settings', 'jardin-toasts' ),
			__( 'Settings', 'jardin-toasts' ),
			'manage_options',
			self::SETTINGS_PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Admin URL of the settings UI.
	 *
	 * @return string
	 */
	public static function get_settings_url() {
		return admin_url( 'admin.php?page=' . self::SETTINGS_PAGE_SLUG );
	}

	/**
	 * Enqueue admin assets on our pages.
	 *
	 * @param string $hook_suffix Screen id.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		$settings_hook = Jardin_Toasts_Post_Type::POST_TYPE . '_page_' . self::SETTINGS_PAGE_SLUG;
		if ( $settings_hook !== $hook_suffix ) {
			return;
		}
		$shell_path = JARDIN_TOASTS_PLUGIN_DIR . 'admin/assets/css/jardin-admin-shell.css';
		$shell_ver  = is_readable( $shell_path ) ? (string) filemtime( $shell_path ) : JARDIN_TOASTS_VERSION;
		wp_enqueue_style(
			'jardin-admin-shell',
			JARDIN_TOASTS_PLUGIN_URL . 'admin/assets/css/jardin-admin-shell.css',
			array( 'dashicons' ),
			$shell_ver
		);
		wp_enqueue_style(
			'jardin-toasts-admin',
			JARDIN_TOASTS_PLUGIN_URL . 'admin/assets/css/admin.css',
			array( 'jardin-admin-shell' ),
			JARDIN_TOASTS_VERSION
		);
		wp_enqueue_media();
		wp_enqueue_script(
			'jardin-toasts-admin',
			JARDIN_TOASTS_PLUGIN_URL . 'admin/assets/js/admin.js',
			array( 'jquery', 'media' ),
			JARDIN_TOASTS_VERSION,
			true
		);
		$placeholder_id = absint( Jardin_Toasts_Settings::get( 'jardin_toasts_placeholder_image_id' ) );
		$placeholder_thumb = $placeholder_id ? wp_get_attachment_image_url( $placeholder_id, 'thumbnail' ) : '';
		wp_localize_script(
			'jardin-toasts-admin',
			'jardinToastsAdmin',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX ),
				'ajaxSyncNow'        => Jardin_Toasts_Keys::AJAX_SYNC_NOW,
				'ajaxTestRss'        => Jardin_Toasts_Keys::AJAX_TEST_RSS,
				'ajaxImportGdprCsv'  => Jardin_Toasts_Keys::AJAX_IMPORT_GDPR_CSV,
				'rssUsername'       => jardin_toasts_parse_username_from_rss_url( jardin_toasts_get_rss_feed_url() ),
				'placeholderId'     => $placeholder_id,
				'placeholderThumb' => $placeholder_thumb ? $placeholder_thumb : '',
				'i18n'              => array(
					'working'        => __( 'Working…', 'jardin-toasts' ),
					'done'           => __( 'Done.', 'jardin-toasts' ),
					'useRssUsername' => __( 'Use username from RSS feed', 'jardin-toasts' ),
					'chooseImage'    => __( 'Choose image', 'jardin-toasts' ),
					'replaceImage'   => __( 'Replace image', 'jardin-toasts' ),
					'removeImage'    => __( 'Remove', 'jardin-toasts' ),
					'testing'        => __( 'Testing…', 'jardin-toasts' ),
					'networkError'   => __( 'Network error.', 'jardin-toasts' ),
					'importGdprPick' => __( 'Choose a CSV file first.', 'jardin-toasts' ),
					'importGdprWorking' => __( 'Importing CSV…', 'jardin-toasts' ),
				),
			)
		);

		$dv_asset = JARDIN_TOASTS_PLUGIN_DIR . 'build/admin-dataviews.asset.php';
		if ( is_readable( $dv_asset ) ) {
			$dv = require $dv_asset;
			wp_enqueue_style(
				'jardin-toasts-admin-dataviews',
				JARDIN_TOASTS_PLUGIN_URL . 'build/admin-dataviews.css',
				array( 'wp-components' ),
				isset( $dv['version'] ) ? (string) $dv['version'] : JARDIN_TOASTS_VERSION
			);
			wp_enqueue_script(
				'jardin-toasts-admin-dataviews',
				JARDIN_TOASTS_PLUGIN_URL . 'build/admin-dataviews.js',
				array_merge( (array) ( $dv['dependencies'] ?? array() ), array( 'wp-components' ) ),
				isset( $dv['version'] ) ? (string) $dv['version'] : JARDIN_TOASTS_VERSION,
				true
			);
			wp_localize_script(
				'jardin-toasts-admin-dataviews',
				'jardinToastsDataviewsSync',
				array(
					'rows' => array(
						array(
							'id'    => 'rss',
							'label' => __( 'RSS feed', 'jardin-toasts' ),
							'value' => jardin_toasts_get_rss_feed_url(),
						),
						array(
							'id'    => 'sync',
							'label' => __( 'Scheduled sync', 'jardin-toasts' ),
							'value' => Jardin_Toasts_Settings::get( 'jardin_toasts_sync_enabled' ) ? __( 'Enabled', 'jardin-toasts' ) : __( 'Disabled', 'jardin-toasts' ),
						),
						array(
							'id'    => 'queue',
							'label' => __( 'Background queue', 'jardin-toasts' ),
							'value' => jardin_toasts_using_action_scheduler() ? 'Action Scheduler' : 'WP-Cron',
						),
					),
				)
			);
		}
	}

	/**
	 * Render tabbed settings.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab_req = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$legacy_tab_map = array(
			'import'  => 'sync',
			'general' => 'display',
			'rating'  => 'display',
		);
		$tab = $tab_req ? $tab_req : 'untappd';
		if ( isset( $legacy_tab_map[ $tab ] ) ) {
			$tab = $legacy_tab_map[ $tab ];
		}
		$allowed = array( 'untappd', 'sync', 'display', 'advanced' );
		if ( ! in_array( $tab, $allowed, true ) ) {
			$tab = 'untappd';
		}

		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'jardin_toasts', 'updated', __( 'Settings saved.', 'jardin-toasts' ), 'success' );
		}

		settings_errors( 'jardin_toasts' );
		include JARDIN_TOASTS_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * AJAX: run RSS sync immediately.
	 *
	 * @return void
	 */
	public function ajax_sync_now() {
		check_ajax_referer( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ) );
		}
		$parser   = new Jardin_Toasts_RSS_Parser();
		$importer = new Jardin_Toasts_Importer();
		$result   = $parser->sync_new_items( $importer, array( 'manual' => true ) );
		if ( is_wp_error( $result ) ) {
			jardin_toasts_send_notification_email(
				'[Jardin Toasts] ' . __( 'RSS sync failed', 'jardin-toasts' ),
				$result->get_error_message(),
				'error'
			);
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Sync finished.', 'jardin-toasts' ) ) );
	}

	/**
	 * AJAX: import check-ins from an Untappd data-export CSV (GDPR bundle, Insider archive, etc.).
	 *
	 * @return void
	 */
	public function ajax_import_gdpr_csv() {
		check_ajax_referer( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ) );
		}

		if ( empty( $_FILES['jardin_toasts_gdpr_csv'] ) || ! isset( $_FILES['jardin_toasts_gdpr_csv']['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'jardin-toasts' ) ) );
		}

		$f = $_FILES['jardin_toasts_gdpr_csv'];
		if ( UPLOAD_ERR_OK !== (int) $f['error'] ) {
			wp_send_json_error( array( 'message' => __( 'Upload failed.', 'jardin-toasts' ) ) );
		}

		$max_upload = (int) wp_max_upload_size();
		$cap        = $max_upload > 0 ? min( $max_upload, 25 * MB_IN_BYTES ) : 25 * MB_IN_BYTES;
		if ( isset( $f['size'] ) && (int) $f['size'] > $cap ) {
			wp_send_json_error( array( 'message' => __( 'File is too large for this site’s upload limit.', 'jardin-toasts' ) ) );
		}

		$name = isset( $f['name'] ) ? (string) $f['name'] : '';
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( 'csv' !== $ext ) {
			wp_send_json_error( array( 'message' => __( 'Please upload a .csv file.', 'jardin-toasts' ) ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- read client-uploaded tmp path only
		$stream = fopen( $f['tmp_name'], 'rb' );
		if ( false === $stream ) {
			wp_send_json_error( array( 'message' => __( 'Could not read the uploaded file.', 'jardin-toasts' ) ) );
		}

		if ( function_exists( 'set_time_limit' ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@set_time_limit( 300 );
		}

		$parser = new Jardin_Toasts_Gdpr_Csv_Importer();
		$stats  = $parser->import_stream( $stream );
		fclose( $stream );

		$msg = sprintf(
			/* translators: 1: imported count, 2: skipped count */
			__( 'Imported %1$d check-in(s); skipped %2$d row(s).', 'jardin-toasts' ),
			(int) $stats['imported'],
			(int) $stats['skipped']
		);
		if ( ! empty( $stats['errors'] ) ) {
			$msg .= ' ' . __( 'Sample issues:', 'jardin-toasts' ) . ' ' . implode( ' | ', array_slice( $stats['errors'], 0, 12 ) );
		}

		wp_send_json_success(
			array(
				'imported' => (int) $stats['imported'],
				'skipped'  => (int) $stats['skipped'],
				'errors'   => $stats['errors'],
				'message'  => $msg,
			)
		);
	}

	/**
	 * AJAX: verify saved RSS feed is readable.
	 *
	 * @return void
	 */
	public function ajax_test_rss() {
		check_ajax_referer( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ) );
		}
		$url = jardin_toasts_get_rss_feed_url();
		if ( ! is_string( $url ) || '' === trim( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'Save an RSS feed URL first.', 'jardin-toasts' ) ) );
		}
		if ( ! function_exists( 'fetch_feed' ) ) {
			require_once ABSPATH . WPINC . '/feed.php';
		}
		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) {
			wp_send_json_error( array( 'message' => $feed->get_error_message() ) );
		}
		$n = $feed->get_item_quantity();
		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of items in the feed snapshot */
					__( 'RSS OK: %d item(s) in this fetch (Untappd may cap the list).', 'jardin-toasts' ),
					$n
				),
			)
		);
	}

}
