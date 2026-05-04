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
 * Class JT_Admin
 */
class JT_Admin {

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
		add_action( 'wp_ajax_' . Jardin_Toasts_Keys::AJAX_CRAWL_DISCOVER, array( $this, 'ajax_crawl_discover' ) );
		add_action( 'wp_ajax_' . Jardin_Toasts_Keys::AJAX_CRAWL_BATCH, array( $this, 'ajax_crawl_batch' ) );
		add_action( 'wp_ajax_' . Jardin_Toasts_Keys::AJAX_TEST_RSS, array( $this, 'ajax_test_rss' ) );
		add_action( 'wp_ajax_' . Jardin_Toasts_Keys::AJAX_TEST_PROFILE, array( $this, 'ajax_test_profile' ) );
		add_action( 'wp_ajax_jt_sync_now', array( $this, 'ajax_sync_now' ) );
		add_action( 'wp_ajax_jt_crawl_discover', array( $this, 'ajax_crawl_discover' ) );
		add_action( 'wp_ajax_jt_crawl_batch', array( $this, 'ajax_crawl_batch' ) );
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
			'jt_jardin_beer_settings',
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
			JT_Post_Type::ADMIN_MENU_SLUG,
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
		$settings_hook = JT_Post_Type::POST_TYPE . '_page_' . self::SETTINGS_PAGE_SLUG;
		if ( $settings_hook !== $hook_suffix ) {
			return;
		}
		$shell_path = JT_PLUGIN_DIR . 'admin/assets/css/jardin-admin-shell.css';
		$shell_ver  = is_readable( $shell_path ) ? (string) filemtime( $shell_path ) : JT_VERSION;
		wp_enqueue_style(
			'jardin-admin-shell',
			JT_PLUGIN_URL . 'admin/assets/css/jardin-admin-shell.css',
			array( 'dashicons' ),
			$shell_ver
		);
		wp_enqueue_style(
			'jardin-toasts-admin',
			JT_PLUGIN_URL . 'admin/assets/css/admin.css',
			array( 'jardin-admin-shell' ),
			JT_VERSION
		);
		wp_enqueue_media();
		wp_enqueue_script(
			'jardin-toasts-admin',
			JT_PLUGIN_URL . 'admin/assets/js/admin.js',
			array( 'jquery', 'media' ),
			JT_VERSION,
			true
		);
		$placeholder_id = absint( JT_Settings::get( 'jt_placeholder_image_id' ) );
		$placeholder_thumb = $placeholder_id ? wp_get_attachment_image_url( $placeholder_id, 'thumbnail' ) : '';
		wp_localize_script(
			'jardin-toasts-admin',
			'jtAdmin',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX ),
				'ajaxSyncNow'        => Jardin_Toasts_Keys::AJAX_SYNC_NOW,
				'ajaxCrawlDiscover'  => Jardin_Toasts_Keys::AJAX_CRAWL_DISCOVER,
				'ajaxCrawlBatch'     => Jardin_Toasts_Keys::AJAX_CRAWL_BATCH,
				'ajaxTestRss'        => Jardin_Toasts_Keys::AJAX_TEST_RSS,
				'ajaxTestProfile'    => Jardin_Toasts_Keys::AJAX_TEST_PROFILE,
				'rssUsername'       => jt_parse_username_from_rss_url( jt_get_rss_feed_url() ),
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
				),
			)
		);

		$dv_asset = JT_PLUGIN_DIR . 'build/admin-dataviews.asset.php';
		if ( is_readable( $dv_asset ) ) {
			$dv = require $dv_asset;
			wp_enqueue_style(
				'jt-admin-dataviews',
				JT_PLUGIN_URL . 'build/admin-dataviews.css',
				array( 'wp-components' ),
				isset( $dv['version'] ) ? (string) $dv['version'] : JT_VERSION
			);
			wp_enqueue_script(
				'jt-admin-dataviews',
				JT_PLUGIN_URL . 'build/admin-dataviews.js',
				array_merge( (array) ( $dv['dependencies'] ?? array() ), array( 'wp-components' ) ),
				isset( $dv['version'] ) ? (string) $dv['version'] : JT_VERSION,
				true
			);
			wp_localize_script(
				'jt-admin-dataviews',
				'jtDataviewsSync',
				array(
					'rows' => array(
						array(
							'id'    => 'rss',
							'label' => __( 'RSS feed', 'jardin-toasts' ),
							'value' => jt_get_rss_feed_url(),
						),
						array(
							'id'    => 'sync',
							'label' => __( 'Scheduled sync', 'jardin-toasts' ),
							'value' => JT_Settings::get( 'jt_sync_enabled' ) ? __( 'Enabled', 'jardin-toasts' ) : __( 'Disabled', 'jardin-toasts' ),
						),
						array(
							'id'    => 'queue',
							'label' => __( 'Background queue', 'jardin-toasts' ),
							'value' => jt_using_action_scheduler() ? 'Action Scheduler' : 'WP-Cron',
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
		include JT_PLUGIN_DIR . 'admin/views/settings-page.php';
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
		$parser   = new JT_RSS_Parser();
		$importer = new JT_Importer();
		$result   = $parser->sync_new_items( $importer, array( 'manual' => true ) );
		if ( is_wp_error( $result ) ) {
			jt_send_notification_email(
				'[Jardin Toasts] ' . __( 'RSS sync failed', 'jardin-toasts' ),
				$result->get_error_message(),
				'error'
			);
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Sync finished.', 'jardin-toasts' ) ) );
	}

	/**
	 * AJAX: discover historical check-ins into queue.
	 *
	 * @return void
	 */
	public function ajax_crawl_discover() {
		check_ajax_referer( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ) );
		}
		$username  = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ), true ) : '';
		$max_pages = isset( $_POST['max_pages'] ) ? absint( $_POST['max_pages'] ) : 10;
		if ( ! $username ) {
			wp_send_json_error( array( 'message' => __( 'Username required.', 'jardin-toasts' ) ) );
		}

		$crawler   = new JT_Crawler();
		$crawl_ids = $crawler->discover_checkins( $username, $max_pages );
		if ( is_wp_error( $crawl_ids ) ) {
			wp_send_json_error( array( 'message' => $crawl_ids->get_error_message() ) );
		}

		$feed_ids = jt_discovery_feed_checkin_ids();
		$merged    = array();
		foreach ( (array) $crawl_ids as $id ) {
			$s = sanitize_text_field( (string) $id );
			if ( '' !== $s ) {
				$merged[ $s ] = true;
			}
		}
		foreach ( (array) $feed_ids as $id ) {
			$s = sanitize_text_field( (string) $id );
			if ( '' !== $s ) {
				$merged[ $s ] = true;
			}
		}
		$ids = array_keys( $merged );

		$crawl_count = count( (array) $crawl_ids );
		$feed_count  = count( (array) $feed_ids );

		$queue = array();
		foreach ( $ids as $id ) {
			if ( ! jt_get_post_id_by_checkin_id( $id ) ) {
				$queue[] = $id;
			}
		}

		update_option(
			'jt_import_checkpoint',
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
				/* translators: %d: number of unique check-in IDs from profile crawl + RSS */
				__( 'Found %d unique check-in ID(s) (public profile crawl plus your configured RSS feed), but each one already exists in WordPress. Nothing new to queue.', 'jardin-toasts' ),
				$discovered
			);
		} elseif ( 0 === $discovered ) {
			$message = __( 'No check-in links were collected. If the problem persists, your host may not be able to reach Untappd from PHP.', 'jardin-toasts' );
		} else {
			$message = sprintf(
				/* translators: 1: newly queued count, 2: total unique IDs from crawl + RSS */
				__( '%1$d new check-in(s) queued for import (out of %2$d unique IDs from the public profile crawl and RSS feed).', 'jardin-toasts' ),
				$queued,
				$discovered
			);
			$message .= ' ';
			$message .= jt_using_action_scheduler()
				? __( 'Imports will continue in the background via Action Scheduler.', 'jardin-toasts' )
				: __( 'Imports will continue in the background via WP-Cron.', 'jardin-toasts' );
		}

		if ( $feed_count > 0 && $crawl_count < $discovered ) {
			$message .= ' ';
			$message .= __( 'Note: Untappd only shows a handful of check-ins on anonymous profile HTML; the RSS feed supplies more recent IDs without signing in.', 'jardin-toasts' );
		}

		$session_note = jt_take_discover_session_notice();
		if ( '' !== $session_note ) {
			$message .= ' ' . $session_note;
		}

		if ( $queued > 0 ) {
			$first_delay = max( 30, absint( get_option( 'jt_import_delay', 3 ) ) * 5 );
			jt_maybe_schedule_background_import_batch( $first_delay );
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
		check_ajax_referer( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ) );
		}

		$cp = get_option( 'jt_import_checkpoint', array() );
		if ( ! is_array( $cp ) || empty( $cp['queue'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Nothing to import. Run discovery first.', 'jardin-toasts' ) ) );
		}

		$batch_size = absint( get_option( 'jt_import_batch_size', 25 ) );
		$batch_size = max( 1, min( 100, $batch_size ) );
		$queue      = $cp['queue'];
		$chunk      = array_splice( $queue, 0, $batch_size );
		$username   = isset( $cp['username'] ) ? (string) $cp['username'] : '';

		$importer = new JT_Importer();
		$imported = 0;
		foreach ( $chunk as $checkin_id ) {
			if ( jt_get_post_id_by_checkin_id( (string) $checkin_id ) ) {
				continue;
			}
			$url  = 'https://untappd.com/user/' . rawurlencode( $username ) . '/checkin/' . $checkin_id;
			$data = array(
				'checkin_id'   => (string) $checkin_id,
				'checkin_url'  => $url,
				'checkin_date' => gmdate( 'c' ),
			);

			$scraper = new JT_Scraper();
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
		update_option( 'jt_import_checkpoint', $cp, false );

		if ( ! empty( $queue ) ) {
			jt_maybe_schedule_background_import_batch();
		}

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
	 * AJAX: verify saved RSS feed is readable.
	 *
	 * @return void
	 */
	public function ajax_test_rss() {
		check_ajax_referer( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ) );
		}
		$url = jt_get_rss_feed_url();
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

	/**
	 * AJAX: verify public profile HTML is reachable (scraping path).
	 *
	 * @return void
	 */
	public function ajax_test_profile() {
		check_ajax_referer( Jardin_Toasts_Keys::NONCE_ADMIN_AJAX, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jardin-toasts' ) ) );
		}
		$user = jt_get_untappd_username();
		if ( ! is_string( $user ) || '' === $user ) {
			wp_send_json_error( array( 'message' => __( 'Save an Untappd username first.', 'jardin-toasts' ) ) );
		}
		$url = 'https://untappd.com/user/' . rawurlencode( $user );
		if ( jt_get_untappd_session_cookie() ) {
			$html = jt_untappd_remote_get_with_session( $url, $url );
			if ( is_wp_error( $html ) ) {
				wp_send_json_error( array( 'message' => $html->get_error_message() ) );
			}
			$code = 200;
			$len  = strlen( $html );
		} else {
			$response = wp_remote_get(
				$url,
				array(
					'timeout'    => 25,
					'user-agent' => jt_http_user_agent_string(),
					'headers'    => jt_untappd_http_headers( '' ),
				)
			);
			if ( is_wp_error( $response ) ) {
				wp_send_json_error( array( 'message' => $response->get_error_message() ) );
			}
			$code = wp_remote_retrieve_response_code( $response );
			$html = wp_remote_retrieve_body( $response );
			$len  = is_string( $html ) ? strlen( $html ) : 0;
		}
		if ( $len < 200 ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: 1: HTTP status code, 2: response size in bytes */
						__( 'Profile response too small (HTTP %1$d, %2$d bytes).', 'jardin-toasts' ),
						(int) $code,
						$len
					),
				)
			);
		}
		$slug = sanitize_user( $user, true );
		$has_links = $slug && preg_match( '#\/user\/' . preg_quote( $slug, '#' ) . '\/checkin\/\d+#i', $html );
		if ( ! $has_links ) {
			wp_send_json_error(
				array(
					'message' => __( 'Profile loaded but no check-in links matched. Untappd markup may have changed, or the username does not match this HTML.', 'jardin-toasts' ),
				)
			);
		}
		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: 1: HTTP status, 2: approximate HTML size */
					__( 'Profile OK (HTTP %1$d, ~%2$s KB HTML, check-in links found).', 'jardin-toasts' ),
					(int) $code,
					(string) max( 1, (int) round( $len / 1024 ) )
				),
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
		$actions[ Jardin_Toasts_Keys::BULK_RESCRAPE ] = __( 'Re-scrape from Untappd', 'jardin-toasts' );
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
		if ( ! is_array( $post_ids ) || ! in_array( $action, array( Jardin_Toasts_Keys::BULK_RESCRAPE, 'jt_bulk_rescrape' ), true ) ) {
			return $redirect_url;
		}
		$cap = (int) apply_filters(
			'jardin_toasts_bulk_rescrape_max_per_request',
			(int) apply_filters( 'jt_bulk_rescrape_max_per_request', 5 )
		);
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
			$res = jt_rescrape_checkin_post( $pid );
			if ( ! is_wp_error( $res ) ) {
				++$done;
			}
		}
		return add_query_arg(
			array(
				'jardin_toasts_rescraped'      => $done,
				'jardin_toasts_rescrape_total' => $total,
				'jardin_toasts_rescrape_cap'   => $cap,
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
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- admin notice display only.
		if ( isset( $_GET['jardin_toasts_rescraped'] ) ) {
			$done  = absint( wp_unslash( $_GET['jardin_toasts_rescraped'] ) );
			$total = isset( $_GET['jardin_toasts_rescrape_total'] ) ? absint( wp_unslash( $_GET['jardin_toasts_rescrape_total'] ) ) : $done;
			$cap   = isset( $_GET['jardin_toasts_rescrape_cap'] ) ? absint( wp_unslash( $_GET['jardin_toasts_rescrape_cap'] ) ) : 5;
		} elseif ( isset( $_GET['jt_rescraped'] ) ) {
			$done  = absint( wp_unslash( $_GET['jt_rescraped'] ) );
			$total = isset( $_GET['jt_rescrape_total'] ) ? absint( wp_unslash( $_GET['jt_rescrape_total'] ) ) : $done;
			$cap   = isset( $_GET['jt_rescrape_cap'] ) ? absint( wp_unslash( $_GET['jt_rescrape_cap'] ) ) : 5;
		} else {
			return;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'edit-beer_checkin' !== $screen->id ) {
			return;
		}
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
