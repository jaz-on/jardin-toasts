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
 * Class JB_Admin
 */
class JB_Admin {

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
		add_action( 'wp_ajax_jb_sync_now', array( $this, 'ajax_sync_now' ) );
		add_action( 'wp_ajax_jb_crawl_discover', array( $this, 'ajax_crawl_discover' ) );
		add_action( 'wp_ajax_jb_crawl_batch', array( $this, 'ajax_crawl_batch' ) );
		add_filter( 'bulk_actions-edit-beer_checkin', array( $this, 'beer_checkin_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-beer_checkin', array( $this, 'handle_beer_checkin_bulk' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'bulk_rescrape_admin_notice' ) );
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
			JB_Post_Type::ADMIN_MENU_SLUG,
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
		$settings_hook = JB_Post_Type::POST_TYPE . '_page_' . self::SETTINGS_PAGE_SLUG;
		if ( $settings_hook !== $hook_suffix ) {
			return;
		}
		$shell_path = JB_PLUGIN_DIR . 'admin/assets/css/jardin-admin-shell.css';
		$shell_ver  = is_readable( $shell_path ) ? (string) filemtime( $shell_path ) : JB_VERSION;
		wp_enqueue_style(
			'jardin-admin-shell',
			JB_PLUGIN_URL . 'admin/assets/css/jardin-admin-shell.css',
			array( 'dashicons' ),
			$shell_ver
		);
		wp_enqueue_style(
			'jardin-toasts-admin',
			JB_PLUGIN_URL . 'admin/assets/css/admin.css',
			array( 'jardin-admin-shell' ),
			JB_VERSION
		);
		wp_enqueue_media();
		wp_enqueue_script(
			'jardin-toasts-admin',
			JB_PLUGIN_URL . 'admin/assets/js/admin.js',
			array( 'jquery', 'media' ),
			JB_VERSION,
			true
		);
		$placeholder_id = absint( JB_Settings::get( 'jb_placeholder_image_id' ) );
		$placeholder_thumb = $placeholder_id ? wp_get_attachment_image_url( $placeholder_id, 'thumbnail' ) : '';
		wp_localize_script(
			'jardin-toasts-admin',
			'jbAdmin',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'jb_admin' ),
				'rssUsername'       => jb_parse_username_from_rss_url( jb_get_rss_feed_url() ),
				'placeholderId'     => $placeholder_id,
				'placeholderThumb' => $placeholder_thumb ? $placeholder_thumb : '',
				'i18n'              => array(
					'working'        => __( 'Working…', 'jardin-toasts' ),
					'done'           => __( 'Done.', 'jardin-toasts' ),
					'useRssUsername' => __( 'Use username from RSS feed', 'jardin-toasts' ),
					'chooseImage'    => __( 'Choose image', 'jardin-toasts' ),
					'replaceImage'   => __( 'Replace image', 'jardin-toasts' ),
					'removeImage'    => __( 'Remove', 'jardin-toasts' ),
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
			add_settings_error( 'jardin_toasts', 'updated', __( 'Settings saved.', 'jardin-toasts' ), 'success' );
		}

		settings_errors( 'jardin_toasts' );
		include JB_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * AJAX: run RSS sync immediately.
	 *
	 * @return void
	 */
	public function ajax_sync_now() {
		check_ajax_referer( 'jb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ), 403 );
		}
		$parser   = new JB_RSS_Parser();
		$importer = new JB_Importer();
		$result   = $parser->sync_new_items( $importer, array( 'manual' => true ) );
		if ( is_wp_error( $result ) ) {
			jb_send_notification_email(
				'[Jardin Toasts] ' . __( 'RSS sync failed', 'jardin-toasts' ),
				$result->get_error_message(),
				'error'
			);
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
		}
		wp_send_json_success( array( 'message' => __( 'Sync finished.', 'jardin-toasts' ) ) );
	}

	/**
	 * AJAX: discover historical check-ins into queue.
	 *
	 * @return void
	 */
	public function ajax_crawl_discover() {
		check_ajax_referer( 'jb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ), 403 );
		}
		$username  = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ), true ) : '';
		$max_pages = isset( $_POST['max_pages'] ) ? absint( $_POST['max_pages'] ) : 10;
		if ( ! $username ) {
			wp_send_json_error( array( 'message' => __( 'Username required.', 'jardin-toasts' ) ), 400 );
		}

		$crawler = new JB_Crawler();
		$ids     = $crawler->discover_checkins( $username, $max_pages );
		if ( is_wp_error( $ids ) ) {
			wp_send_json_error( array( 'message' => $ids->get_error_message() ), 500 );
		}

		$queue = array();
		foreach ( $ids as $id ) {
			if ( ! jb_get_post_id_by_checkin_id( $id ) ) {
				$queue[] = $id;
			}
		}

		update_option(
			'jb_import_checkpoint',
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

		$discovered = count( $ids );
		$queued     = count( $queue );

		if ( $discovered > 0 && 0 === $queued ) {
			$message = sprintf(
				/* translators: %d: number of check-ins found on Untappd profile pages */
				__( 'Found %d check-in(s) on your profile, but each one already exists in WordPress (same Untappd check-in ID). Nothing new to queue.', 'jardin-toasts' ),
				$discovered
			);
		} elseif ( 0 === $discovered ) {
			$message = __( 'No check-in links were collected. If the problem persists, your host may not be able to reach Untappd from PHP.', 'jardin-toasts' );
		} else {
			$message = sprintf(
				/* translators: 1: newly queued count, 2: total discovered on profile */
				__( '%1$d new check-in(s) queued for import (out of %2$d found on the crawled profile pages).', 'jardin-toasts' ),
				$queued,
				$discovered
			);
		}

		wp_send_json_success(
			array(
				'queued'     => $queued,
				'discovered' => $discovered,
				'message'    => $message,
			)
		);
	}

	/**
	 * AJAX: import next batch from queue.
	 *
	 * @return void
	 */
	public function ajax_crawl_batch() {
		check_ajax_referer( 'jb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ), 403 );
		}

		$cp = get_option( 'jb_import_checkpoint', array() );
		if ( ! is_array( $cp ) || empty( $cp['queue'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Nothing to import. Run discovery first.', 'jardin-toasts' ) ), 400 );
		}

		$batch_size = absint( get_option( 'jb_import_batch_size', 25 ) );
		$batch_size = max( 1, min( 100, $batch_size ) );
		$queue      = $cp['queue'];
		$chunk      = array_splice( $queue, 0, $batch_size );
		$username   = isset( $cp['username'] ) ? (string) $cp['username'] : '';

		$importer = new JB_Importer();
		$imported = 0;
		foreach ( $chunk as $checkin_id ) {
			if ( jb_get_post_id_by_checkin_id( (string) $checkin_id ) ) {
				continue;
			}
			$url  = 'https://untappd.com/user/' . rawurlencode( $username ) . '/checkin/' . $checkin_id;
			$data = array(
				'checkin_id'   => (string) $checkin_id,
				'checkin_url'  => $url,
				'checkin_date' => gmdate( 'c' ),
			);

			$scraper = new JB_Scraper();
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
		update_option( 'jb_import_checkpoint', $cp, false );

		wp_send_json_success(
			array(
				'imported'     => $imported,
				'remaining'    => count( $queue ),
				'total_imported' => $cp['total_imported'],
				'done'         => empty( $queue ),
			)
		);
	}

	/**
	 * Bulk action label for check-in list.
	 *
	 * @param array<string, string> $actions Actions.
	 * @return array<string, string>
	 */
	public function beer_checkin_bulk_actions( $actions ) {
		$actions['jb_bulk_rescrape'] = __( 'Re-scrape from Untappd', 'jardin-toasts' );
		return $actions;
	}

	/**
	 * Run re-scrape on selected check-ins (capped per request).
	 *
	 * @param string $redirect_url Redirect URL.
	 * @param string $action       Action name.
	 * @param array<int, int>      $post_ids   Post IDs.
	 * @return string
	 */
	public function handle_beer_checkin_bulk( $redirect_url, $action, $post_ids ) {
		if ( 'jb_bulk_rescrape' !== $action || ! is_array( $post_ids ) ) {
			return $redirect_url;
		}
		$cap   = (int) apply_filters( 'jb_bulk_rescrape_max_per_request', 5 );
		$cap   = max( 1, min( 25, $cap ) );
		$done  = 0;
		$total = count( $post_ids );
		foreach ( $post_ids as $pid ) {
			if ( $done >= $cap ) {
				break;
			}
			$pid = absint( $pid );
			if ( ! $pid || ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}
			$res = jb_rescrape_checkin_post( $pid );
			if ( ! is_wp_error( $res ) ) {
				++$done;
			}
		}
		return add_query_arg(
			array(
				'jb_rescraped'      => $done,
				'jb_rescrape_total' => $total,
				'jb_rescrape_cap'   => $cap,
			),
			$redirect_url
		);
	}

	/**
	 * Notice after bulk re-scrape.
	 *
	 * @return void
	 */
	public function bulk_rescrape_admin_notice() {
		if ( ! isset( $_GET['jb_rescraped'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'edit-beer_checkin' !== $screen->id ) {
			return;
		}
		$done = absint( wp_unslash( $_GET['jb_rescraped'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$total = isset( $_GET['jb_rescrape_total'] ) ? absint( wp_unslash( $_GET['jb_rescrape_total'] ) ) : $done; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cap   = isset( $_GET['jb_rescrape_cap'] ) ? absint( wp_unslash( $_GET['jb_rescrape_cap'] ) ) : 5; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $total > $cap && $done === $cap ) {
			echo '<div class="notice notice-warning is-dismissible"><p>';
			echo esc_html(
				sprintf(
					/* translators: 1: processed count, 2: cap, 3: selected count */
					__( 'Re-scraped %1$d check-in(s) (limit %2$d per run). Select again to process more of the %3$d selected.', 'jardin-toasts' ),
					$done,
					$cap,
					$total
				)
			);
			echo '</p></div>';
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html(
			sprintf(
				/* translators: %d: number of check-ins */
				_n( 'Re-scraped %d check-in from Untappd.', 'Re-scraped %d check-ins from Untappd.', $done, 'jardin-toasts' ),
				$done
			)
		);
		echo '</p></div>';
	}
}
