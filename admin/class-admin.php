<?php
/**
 * Admin UI: settings, import, logs.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BJ_Admin
 */
class BJ_Admin {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_bj_sync_now', array( $this, 'ajax_sync_now' ) );
		add_action( 'wp_ajax_bj_crawl_discover', array( $this, 'ajax_crawl_discover' ) );
		add_action( 'wp_ajax_bj_crawl_batch', array( $this, 'ajax_crawl_batch' ) );
	}

	/**
	 * Top-level menu and settings page.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Beer Journal', 'beer-journal' ),
			__( 'Beer Journal', 'beer-journal' ),
			'manage_options',
			'beer-journal',
			array( $this, 'render_settings_page' ),
			'dashicons-beer',
			58
		);

		add_submenu_page(
			'beer-journal',
			__( 'Beer Journal Settings', 'beer-journal' ),
			__( 'Settings', 'beer-journal' ),
			'manage_options',
			'beer-journal',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets on our pages.
	 *
	 * @param string $hook_suffix Screen id.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'beer-journal' ) ) {
			return;
		}
		wp_enqueue_style(
			'beer-journal-admin',
			BJ_PLUGIN_URL . 'admin/assets/css/admin.css',
			array(),
			BJ_VERSION
		);
		wp_enqueue_script(
			'beer-journal-admin',
			BJ_PLUGIN_URL . 'admin/assets/js/admin.js',
			array( 'jquery' ),
			BJ_VERSION,
			true
		);
		wp_localize_script(
			'beer-journal-admin',
			'bjAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'bj_admin' ),
				'i18n'    => array(
					'working' => __( 'Working…', 'beer-journal' ),
					'done'    => __( 'Done.', 'beer-journal' ),
				),
			)
		);
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

		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'sync'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$allowed = array( 'sync', 'import', 'general', 'rating', 'advanced' );
		if ( ! in_array( $tab, $allowed, true ) ) {
			$tab = 'sync';
		}

		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'beer_journal', 'updated', __( 'Settings saved.', 'beer-journal' ), 'success' );
		}

		settings_errors( 'beer_journal' );
		include BJ_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * AJAX: run RSS sync immediately.
	 *
	 * @return void
	 */
	public function ajax_sync_now() {
		check_ajax_referer( 'bj_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'beer-journal' ) ), 403 );
		}
		$parser   = new BJ_RSS_Parser();
		$importer = new BJ_Importer();
		$result   = $parser->sync_new_items( $importer );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
		}
		wp_send_json_success( array( 'message' => __( 'Sync finished.', 'beer-journal' ) ) );
	}

	/**
	 * AJAX: discover historical check-ins into queue.
	 *
	 * @return void
	 */
	public function ajax_crawl_discover() {
		check_ajax_referer( 'bj_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'beer-journal' ) ), 403 );
		}
		$username  = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ), true ) : '';
		$max_pages = isset( $_POST['max_pages'] ) ? absint( $_POST['max_pages'] ) : 10;
		if ( ! $username ) {
			wp_send_json_error( array( 'message' => __( 'Username required.', 'beer-journal' ) ), 400 );
		}

		$crawler = new BJ_Crawler();
		$ids     = $crawler->discover_checkins( $username, $max_pages );
		if ( is_wp_error( $ids ) ) {
			wp_send_json_error( array( 'message' => $ids->get_error_message() ), 500 );
		}

		$queue = array();
		foreach ( $ids as $id ) {
			if ( ! bj_get_post_id_by_checkin_id( $id ) ) {
				$queue[] = $id;
			}
		}

		update_option(
			'bj_import_checkpoint',
			array(
				'queue'          => $queue,
				'username'       => $username,
				'status'         => 'ready',
				'total_queued'   => count( $queue ),
				'total_imported' => 0,
				'discovered_at'  => time(),
			),
			false
		);

		wp_send_json_success(
			array(
				'queued' => count( $queue ),
				'message' => sprintf(
					/* translators: %d: number of check-ins */
					_n( '%d new check-in queued for import.', '%d new check-ins queued for import.', count( $queue ), 'beer-journal' ),
					count( $queue )
				),
			)
		);
	}

	/**
	 * AJAX: import next batch from queue.
	 *
	 * @return void
	 */
	public function ajax_crawl_batch() {
		check_ajax_referer( 'bj_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'beer-journal' ) ), 403 );
		}

		$cp = get_option( 'bj_import_checkpoint', array() );
		if ( ! is_array( $cp ) || empty( $cp['queue'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Nothing to import. Run discovery first.', 'beer-journal' ) ), 400 );
		}

		$batch_size = absint( get_option( 'bj_import_batch_size', 25 ) );
		$batch_size = max( 1, min( 100, $batch_size ) );
		$queue      = $cp['queue'];
		$chunk      = array_splice( $queue, 0, $batch_size );
		$username   = isset( $cp['username'] ) ? (string) $cp['username'] : '';

		$importer = new BJ_Importer();
		$imported = 0;
		foreach ( $chunk as $checkin_id ) {
			if ( bj_get_post_id_by_checkin_id( (string) $checkin_id ) ) {
				continue;
			}
			$url  = 'https://untappd.com/user/' . rawurlencode( $username ) . '/checkin/' . $checkin_id;
			$data = array(
				'checkin_id'   => (string) $checkin_id,
				'checkin_url'  => $url,
				'checkin_date' => gmdate( 'c' ),
			);

			$scraper = new BJ_Scraper();
			$scraped = $scraper->scrape_checkin_url( $url );
			if ( ! is_wp_error( $scraped ) ) {
				$data = array_merge( $data, $scraped );
			}

			$res = $importer->import_checkin_data( $data, 'crawler' );
			if ( ! is_wp_error( $res ) ) {
				++$imported;
			}
		}

		$cp['queue']          = $queue;
		$cp['total_imported'] = isset( $cp['total_imported'] ) ? absint( $cp['total_imported'] ) + $imported : $imported;
		$cp['status']         = empty( $queue ) ? 'done' : 'running';
		$cp['last_run']       = time();
		update_option( 'bj_import_checkpoint', $cp, false );

		wp_send_json_success(
			array(
				'imported'     => $imported,
				'remaining'    => count( $queue ),
				'total_imported' => $cp['total_imported'],
				'done'         => empty( $queue ),
			)
		);
	}
}
