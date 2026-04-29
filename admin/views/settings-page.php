<?php
/**
 * Tabbed settings page markup.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = JB_Admin::get_settings_url();
$tabs     = array(
	'sync'     => __( 'Synchronization', 'jardin-toasts' ),
	'import'   => __( 'Historical import', 'jardin-toasts' ),
	'general'  => __( 'Display & content', 'jardin-toasts' ),
	'rating'   => __( 'Ratings', 'jardin-toasts' ),
	'advanced' => __( 'Advanced', 'jardin-toasts' ),
);

$tab_intros = array(
	'sync'     => __( 'Connect your Untappd RSS feed and let WordPress pull new check-ins on a calm schedule. RSS only lists your latest items; full details still come from scraping.', 'jardin-toasts' ),
	'import'   => __( 'Backfill older check-ins by crawling your public profile. Use small batches and delays to stay polite to Untappd’s servers.', 'jardin-toasts' ),
	'general'  => __( 'Control how archives look and what gets stored with each check-in on the front of your site.', 'jardin-toasts' ),
	'rating'   => __( 'Untappd stores fractional ratings. Map them to whole stars for display using the rules below (or override with code).', 'jardin-toasts' ),
	'advanced' => __( 'Fine-tune scraping pace, structured data, notifications, and troubleshooting logs.', 'jardin-toasts' ),
);
?>
<div class="wrap jb-admin-wrap">
	<div class="jb-settings-hero">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p class="jb-settings-hero__lead"><?php esc_html_e( 'Untappd → WordPress: sync, import, and present your beer journal with theme-friendly templates.', 'jardin-toasts' ); ?></p>
	</div>

	<nav class="nav-tab-wrapper jb-nav-tabs" aria-label="<?php esc_attr_e( 'Jardin Toasts settings sections', 'jardin-toasts' ); ?>">
		<?php foreach ( $tabs as $id => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $id, $base_url ) ); ?>" class="nav-tab <?php echo $tab === $id ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="options.php" class="jb-settings-form">
		<?php settings_fields( JB_Settings::OPTION_GROUP ); ?>

		<div class="jb-tab-panel">
			<p class="jb-tab-intro"><?php echo isset( $tab_intros[ $tab ] ) ? esc_html( $tab_intros[ $tab ] ) : ''; ?></p>

			<?php
			/*
			 * Render every settings tab in the same form so options.php receives all keys on save.
			 * (Otherwise saving e.g. "Display & content" would POST missing fields and WordPress clears them.)
			 */
			?>
			<div class="jb-tab-panel-body"<?php echo 'sync' !== $tab ? ' hidden' : ''; ?> data-jb-tab="sync">
				<?php include JB_PLUGIN_DIR . 'admin/views/stats-box.php'; ?>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'RSS feed & scheduling', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'The feed URL is pre-filled with a working example. Replace it with your own from Untappd → Account if you use a different profile.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jb_rss_feed_url"><?php esc_html_e( 'Untappd RSS feed URL', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jb_rss_feed_url" id="jb_rss_feed_url" type="url" class="large-text code" value="<?php echo esc_attr( jb_get_rss_feed_url() ); ?>" autocomplete="off" />
									<p class="description"><?php esc_html_e( 'Public RSS (about 25 recent check-ins). Used by automatic sync and “Run sync now”.', 'jardin-toasts' ); ?></p>
									<p class="description"><?php echo wp_kses_post( sprintf( /* translators: %s: link to Import tab */ __( 'Historical import uses the same Untappd profile. Set your username on the <a href="%s">Import</a> tab (it should match this feed).', 'jardin-toasts' ), esc_url( add_query_arg( 'tab', 'import', $base_url ) ) . '#jb-import-profile' ) ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Automatic sync', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_sync_enabled" value="0" />
										<input name="jb_sync_enabled" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_sync_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Enable scheduled RSS synchronization', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php
									if ( jb_using_action_scheduler() ) {
										esc_html_e( 'Uses Action Scheduler with adaptive intervals: more frequent when you check in often, lighter when you are quiet. Inspect the jardin-toasts group under Tools → Scheduled Actions (menu location may differ if WooCommerce owns Action Scheduler).', 'jardin-toasts' );
									} else {
										esc_html_e( 'Uses adaptive WP-Cron: more frequent when you check in often, lighter when you are quiet. Low-traffic sites should hit wp-cron.php from a real system cron, or install the Action Scheduler plugin for a proper job queue.', 'jardin-toasts' );
									}
									?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jb-panel jb-panel--actions">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Run a sync now', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Fetches the RSS feed immediately and imports any new items (same code path as automatic scheduled sync).', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body jb-panel__body--inline">
						<button type="button" class="button button-secondary" id="jb-sync-now"><?php esc_html_e( 'Run sync now', 'jardin-toasts' ); ?></button>
						<span class="jb-ajax-status" id="jb-sync-status" aria-live="polite"></span>
					</div>
				</div>

			</div>
			<div class="jb-tab-panel-body"<?php echo 'import' !== $tab ? ' hidden' : ''; ?> data-jb-tab="import">
				<?php
				$rss_username = jb_parse_username_from_rss_url( jb_get_rss_feed_url() );
				$batch_current  = (int) JB_Settings::get( 'jb_import_batch_size' );
				$delay_current  = (int) JB_Settings::get( 'jb_import_delay' );
				$batch_choices  = array(
					10  => __( '10 check-ins — small steps', 'jardin-toasts' ),
					15  => __( '15 check-ins — light', 'jardin-toasts' ),
					25  => __( '25 check-ins — balanced (recommended)', 'jardin-toasts' ),
					40  => __( '40 check-ins — fewer clicks', 'jardin-toasts' ),
					50  => __( '50 check-ins — large (may time out)', 'jardin-toasts' ),
				);
				$delay_choices  = array(
					0 => __( 'No pause (fast hosts only)', 'jardin-toasts' ),
					1 => __( '1 second between requests', 'jardin-toasts' ),
					2 => __( '2 seconds — gentle', 'jardin-toasts' ),
					3 => __( '3 seconds — polite (default)', 'jardin-toasts' ),
					5 => __( '5 seconds — very safe', 'jardin-toasts' ),
					8 => __( '8 seconds — slowest', 'jardin-toasts' ),
				);
				if ( ! array_key_exists( $batch_current, $batch_choices ) ) {
					$batch_choices[ $batch_current ] = sprintf(
						/* translators: %d: number of check-ins */
						__( '%d check-ins (current)', 'jardin-toasts' ),
						$batch_current
					);
					ksort( $batch_choices, SORT_NUMERIC );
				}
				if ( ! array_key_exists( $delay_current, $delay_choices ) ) {
					$delay_choices[ $delay_current ] = sprintf(
						/* translators: %d: seconds */
						__( '%d seconds (current)', 'jardin-toasts' ),
						$delay_current
					);
					ksort( $delay_choices, SORT_NUMERIC );
				}
				?>

				<div class="jb-panel" id="jb-import-profile">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Profile & batching', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Discovery loads your public profile HTML and collects check-in links. Only the username is needed — the same person as in your RSS feed on the Synchronization tab.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jb_untappd_username"><?php esc_html_e( 'Untappd username', 'jardin-toasts' ); ?></label></th>
								<td>
									<p class="jb-field-row">
										<input name="jb_untappd_username" id="jb_untappd_username" type="text" class="regular-text" value="<?php echo esc_attr( jb_get_untappd_username() ); ?>" autocomplete="username" spellcheck="false" />
										<?php if ( '' !== $rss_username ) : ?>
											<button type="button" class="button button-secondary" id="jb-use-rss-username"><?php esc_html_e( 'Use username from RSS feed', 'jardin-toasts' ); ?></button>
										<?php endif; ?>
									</p>
									<p class="description"><?php esc_html_e( 'Enter the profile slug only (e.g. jaz_on), not the full URL. It must match the user segment in your RSS URL: untappd.com/rss/user/…', 'jardin-toasts' ); ?></p>
									<?php if ( '' !== $rss_username ) : ?>
										<p class="description"><?php echo esc_html( sprintf( /* translators: %s: username */ __( 'Detected from your saved RSS URL: %s', 'jardin-toasts' ), $rss_username ) ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jb_import_batch_size"><?php esc_html_e( 'Batch size', 'jardin-toasts' ); ?></label></th>
								<td>
									<select name="jb_import_batch_size" id="jb_import_batch_size">
										<?php foreach ( $batch_choices as $val => $label ) : ?>
											<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( $batch_current, $val ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'How many check-ins to import each time you click “Import next batch”. Smaller batches finish faster per request; larger ones mean fewer clicks for big backfills.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jb_import_delay"><?php esc_html_e( 'Pause between requests', 'jardin-toasts' ); ?></label></th>
								<td>
									<select name="jb_import_delay" id="jb_import_delay">
										<?php foreach ( $delay_choices as $val => $label ) : ?>
											<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( $delay_current, $val ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Wait time after each HTTP request during discovery and import. Longer pauses are kinder to Untappd and your host; shorter pauses finish sooner.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jb_import_mode"><?php esc_html_e( 'Import mode', 'jardin-toasts' ); ?></label></th>
								<td>
									<select name="jb_import_mode" id="jb_import_mode">
										<option value="manual" <?php selected( JB_Settings::get( 'jb_import_mode' ), 'manual' ); ?>><?php esc_html_e( 'Manual — AJAX batches (recommended)', 'jardin-toasts' ); ?></option>
										<option value="background" <?php selected( JB_Settings::get( 'jb_import_mode' ), 'background' ); ?>><?php echo esc_html( jb_using_action_scheduler() ? __( 'Background — Action Scheduler', 'jardin-toasts' ) : __( 'Background — WP-Cron', 'jardin-toasts' ) ); ?></option>
									</select>
									<p class="description"><?php
									if ( jb_using_action_scheduler() ) {
										esc_html_e( 'Manual keeps you in control; background runs import batches via Action Scheduler when the queue has work.', 'jardin-toasts' );
									} else {
										esc_html_e( 'Manual keeps you in control; background spreads work across WP-Cron single events when the queue has work (less reliable on low-traffic sites).', 'jardin-toasts' );
									}
									?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jb-panel jb-panel--actions">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Discovery & import', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Run discovery once to build a queue, then import batches until the queue is empty.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body jb-panel__body--inline">
						<button type="button" class="button" id="jb-discover"><?php esc_html_e( 'Discover check-ins', 'jardin-toasts' ); ?></button>
						<button type="button" class="button button-primary" id="jb-import-batch"><?php esc_html_e( 'Import next batch', 'jardin-toasts' ); ?></button>
						<span class="jb-ajax-status" id="jb-import-status" aria-live="polite"></span>
					</div>
				</div>
				<input type="hidden" id="jb-discover-max-pages" value="15" />

			</div>
			<div class="jb-tab-panel-body"<?php echo 'general' !== $tab ? ' hidden' : ''; ?> data-jb-tab="general">

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Archives & layout', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Choose how lists of check-ins appear on the main journal and taxonomy pages.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jb_archive_layout"><?php esc_html_e( 'Archive layout', 'jardin-toasts' ); ?></label></th>
								<td>
									<select name="jb_archive_layout" id="jb_archive_layout">
										<option value="grid" <?php selected( JB_Settings::get( 'jb_archive_layout' ), 'grid' ); ?>><?php esc_html_e( 'Grid — cards with photos', 'jardin-toasts' ); ?></option>
										<option value="table" <?php selected( JB_Settings::get( 'jb_archive_layout' ), 'table' ); ?>><?php esc_html_e( 'Table — compact rows', 'jardin-toasts' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Applies to the beer check-in archive and beer style / brewery / venue listings.', 'jardin-toasts' ); ?></p>
									<?php
									$archive_url = jb_get_checkin_archive_url();
									?>
									<p class="description jb-archive-url">
										<?php echo esc_html__( 'Public journal URL:', 'jardin-toasts' ); ?>
										<a href="<?php echo esc_url( $archive_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $archive_url ); ?></a>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Imported content', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Control media and taxonomy data saved with each check-in. Check-in notes are stored as post content with automatic paragraphs for plain text.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Beer photos', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_import_images" value="0" />
										<input name="jb_import_images" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_import_images' ) ); ?> />
										<span><?php esc_html_e( 'Yes — import beer photos into the Media Library', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default for new installs. Turn off if you only want text.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Venues', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_import_venues" value="0" />
										<input name="jb_import_venues" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_import_venues' ) ); ?> />
										<span><?php esc_html_e( 'Yes — create venue taxonomy terms when a location is present', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Lets you browse check-ins by place.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Social stats', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_import_social_data" value="0" />
										<input name="jb_import_social_data" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_import_social_data' ) ); ?> />
										<span><?php esc_html_e( 'Yes — store toast counts when Untappd exposes them', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Optional engagement metadata.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Fallback image', 'jardin-toasts' ); ?></th>
								<td>
									<?php
									$jb_use_ph = JB_Settings::get( 'jb_use_placeholder_image' );
									?>
									<label class="jb-toggle">
										<input type="hidden" name="jb_use_placeholder_image" value="0" />
										<input name="jb_use_placeholder_image" type="checkbox" value="1" id="jb_use_placeholder_image" <?php checked( $jb_use_ph ); ?> />
										<span><?php esc_html_e( 'Use a fallback image when Untappd download fails (on by default). Uncheck to leave the post without a featured image.', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'Pick a generic beer or logo from your Media Library (no attachment ID to type). There is no built-in remote beer-photo API (keys, licensing, reliability); you can wire Open Food Facts, Wikimedia, or your own source in PHP with the jb_placeholder_attachment_id filter, returning a Media Library attachment ID you created.', 'jardin-toasts' ); ?></p>
									<div class="jb-placeholder-picker" id="jb-placeholder-picker" style="<?php echo $jb_use_ph ? '' : 'display:none;'; ?>">
										<input type="hidden" name="jb_placeholder_image_id" id="jb_placeholder_image_id" value="<?php echo esc_attr( (string) (int) JB_Settings::get( 'jb_placeholder_image_id' ) ); ?>" />
										<p class="jb-placeholder-picker__actions">
											<button type="button" class="button" id="jb-placeholder-select"><?php esc_html_e( 'Choose image', 'jardin-toasts' ); ?></button>
											<button type="button" class="button-link" id="jb-placeholder-clear"><?php esc_html_e( 'Remove', 'jardin-toasts' ); ?></button>
										</p>
										<div class="jb-placeholder-picker__preview" id="jb-placeholder-preview" aria-live="polite"></div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>

			</div>
			<div class="jb-tab-panel-body"<?php echo 'rating' !== $tab ? ' hidden' : ''; ?> data-jb-tab="rating">
				<?php
				$rule_template = jb_get_default_rating_rules();
				$rules         = JB_Settings::get( 'jb_rating_rules' );
				if ( ! is_array( $rules ) ) {
					$rules = $rule_template;
				}
				$rules = array_values( $rules );
				while ( count( $rules ) < count( $rule_template ) ) {
					$rules[] = $rule_template[ count( $rules ) ];
				}
				$rules = array_slice( $rules, 0, count( $rule_template ) );

				$default_labels = jb_get_default_rating_labels();
				$labels         = JB_Settings::get( 'jb_rating_labels' );
				if ( ! is_array( $labels ) ) {
					$labels = $default_labels;
				}
				for ( $li = 0; $li <= 5; $li++ ) {
					if ( ! isset( $labels[ $li ] ) ) {
						$labels[ $li ] = isset( $default_labels[ $li ] ) ? $default_labels[ $li ] : '';
					}
				}
				?>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Star mapping', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Map Untappd’s 0–5 raw score into whole stars. Rules are tested in order; the first matching min/max band wins. The jb_rating_rules filter can still override in code.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Use mapping rules', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_rating_rounding_enabled" value="0" />
										<input name="jb_rating_rounding_enabled" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_rating_rounding_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Map fractional Untappd ratings to whole stars', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
						</table>

						<div class="jb-rating-edit">
							<h3><?php esc_html_e( 'Raw score bands', 'jardin-toasts' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Each row is one band: if the raw rating is between min and max (inclusive), it maps to the chosen star level.', 'jardin-toasts' ); ?></p>
							<table class="widefat striped jb-rating-edit-table">
								<thead>
									<tr>
										<th scope="col" class="jb-rating-edit-table__n"><?php esc_html_e( '#', 'jardin-toasts' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Min (raw)', 'jardin-toasts' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Max (raw)', 'jardin-toasts' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Star level', 'jardin-toasts' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $rules as $i => $rule ) {
										$rmin   = isset( $rule['min'] ) ? (float) $rule['min'] : 0;
										$rmax   = isset( $rule['max'] ) ? (float) $rule['max'] : 0;
										$rround = isset( $rule['round'] ) ? absint( $rule['round'] ) : 0;
										?>
										<tr>
											<td class="jb-rating-edit-table__n"><?php echo esc_html( (string) ( $i + 1 ) ); ?></td>
											<td>
												<label class="screen-reader-text" for="jb-rating-min-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d min', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<input name="jb_rating_rules[<?php echo esc_attr( (string) $i ); ?>][min]" id="jb-rating-min-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmin ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="jb-rating-max-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d max', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<input name="jb_rating_rules[<?php echo esc_attr( (string) $i ); ?>][max]" id="jb-rating-max-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmax ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="jb-rating-round-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d stars', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<select name="jb_rating_rules[<?php echo esc_attr( (string) $i ); ?>][round]" id="jb-rating-round-<?php echo esc_attr( (string) $i ); ?>">
													<?php for ( $s = 0; $s <= 5; $s++ ) : ?>
														<option value="<?php echo esc_attr( (string) $s ); ?>" <?php selected( $rround, $s ); ?>><?php echo esc_html( (string) $s ); ?></option>
													<?php endfor; ?>
												</select>
											</td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>

							<h3><?php esc_html_e( 'Labels per star level', 'jardin-toasts' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Optional text for each rounded level (for themes, blocks, or future UI).', 'jardin-toasts' ); ?></p>
							<table class="widefat striped jb-rating-edit-table jb-rating-edit-table--labels" role="presentation">
								<tbody>
									<?php
									for ( $s = 0; $s <= 5; $s++ ) {
										$lid = 'jb-rating-label-' . $s;
										?>
										<tr>
											<th scope="row">
												<label for="<?php echo esc_attr( $lid ); ?>"><?php echo esc_html( sprintf( /* translators: %d: star count 0-5 */ __( '%d stars', 'jardin-toasts' ), $s ) ); ?></label>
											</th>
											<td>
												<input name="jb_rating_labels[<?php echo esc_attr( (string) $s ); ?>]" id="<?php echo esc_attr( $lid ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( (string) $labels[ $s ] ); ?>" />
											</td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>
			<div class="jb-tab-panel-body"<?php echo 'advanced' !== $tab ? ' hidden' : ''; ?> data-jb-tab="advanced">

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Scraping & performance', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'These settings throttle how aggressively the plugin hits Untappd when it downloads check-in HTML (automatic RSS sync, “Run sync now”, and background import). They are separate from the Synchronization tab (feed URL and whether RSS runs) and from Historical import → “Pause between requests”, which only spaces out clicks in that admin wizard.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jb_scraping_delay"><?php esc_html_e( 'Scraping delay', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jb_scraping_delay" id="jb_scraping_delay" type="number" min="1" class="small-text" value="<?php echo esc_attr( (string) (int) JB_Settings::get( 'jb_scraping_delay' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'seconds', 'jardin-toasts' ); ?></span>
									<p class="description"><?php esc_html_e( 'Minimum 1 second (values below 1 are saved as 1). Higher values are gentler; lower values are faster but easier to rate-limit.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jb_rss_max_per_run"><?php esc_html_e( 'RSS imports per scheduled sync', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jb_rss_max_per_run" id="jb_rss_max_per_run" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr( (string) (int) JB_Settings::get( 'jb_rss_max_per_run' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Minimum 1 per run (values below 1 are saved as 1). Each automatic sync scrapes at most this many new check-ins; the rest stay in a queue and are processed by follow-up events. “Run sync now” uses a higher limit.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Sync health', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Quick signals when diagnosing RSS backlog or failed scrapes.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<ul class="jb-sync-health-list">
							<li>
								<strong><?php esc_html_e( 'RSS queue depth', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( count( jb_get_rss_sync_queue() ) ) ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Draft check-ins needing data', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( jb_count_draft_incomplete_checkins() ) ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Scraper markup version', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( (string) (int) JB_Scraper_Config::MARKUP_VERSION ); ?>
							</li>
						</ul>
						<p class="description"><?php esc_html_e( 'Recent log lines appear below under “Log file (today)”. Enable debug logging for more detail.', 'jardin-toasts' ); ?></p>
					</div>
				</div>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'SEO & semantics', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Optional structured data and microformats for themes and parsers.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Schema.org JSON-LD', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_schema_enabled" value="0" />
										<input name="jb_schema_enabled" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_schema_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Output Review structured data on single check-in pages', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Microformats', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_microformats_enabled" value="0" />
										<input name="jb_microformats_enabled" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_microformats_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Add h-entry / e-content classes for IndieWeb-style consumers', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jb-panel jb-panel--notice">
					<div class="jb-panel__body">
						<p class="jb-panel__notice-text">
							<span class="dashicons dashicons-info" aria-hidden="true"></span>
							<?php esc_html_e( 'Jardin Toasts reads public Untappd RSS and HTML only. Untappd may change pages at any time. You are responsible for complying with Untappd’s terms and for content you republish. See docs/legal/scraping-notice.md in the plugin.', 'jardin-toasts' ); ?>
						</p>
					</div>
				</div>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Email notifications', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Optional alerts when sync completes or fails.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'On successful sync', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_notify_on_sync" value="0" />
										<input name="jb_notify_on_sync" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_notify_on_sync' ) ); ?> />
										<span><?php esc_html_e( 'Email when new check-ins are imported via RSS', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'On RSS error', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_notify_on_error" value="0" />
										<input name="jb_notify_on_error" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_notify_on_error' ) ); ?> />
										<span><?php esc_html_e( 'Email when the feed cannot be fetched', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jb_notification_email"><?php esc_html_e( 'Notification email', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jb_notification_email" id="jb_notification_email" type="email" class="regular-text" value="<?php echo esc_attr( (string) JB_Settings::get( 'jb_notification_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Leave empty to use the site admin email.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Logging & debug', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Verbose logging can help support; keep retention sensible on shared hosting.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Debug logging', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jb-toggle">
										<input type="hidden" name="jb_debug_mode" value="0" />
										<input name="jb_debug_mode" type="checkbox" value="1" <?php checked( JB_Settings::get( 'jb_debug_mode' ) ); ?> />
										<span><?php esc_html_e( 'Write extra detail to the log file', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jb_log_retention_days"><?php esc_html_e( 'Log retention', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jb_log_retention_days" id="jb_log_retention_days" type="number" min="0" class="small-text" value="<?php echo esc_attr( (string) (int) JB_Settings::get( 'jb_log_retention_days' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'days (0 = disable rotation hints)', 'jardin-toasts' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jb-panel">
					<div class="jb-panel__header">
						<h2 class="jb-panel__title"><?php esc_html_e( 'Log file (today)', 'jardin-toasts' ); ?></h2>
						<p class="jb-panel__summary"><?php esc_html_e( 'Tail of the current log. Paths may differ on your host.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jb-panel__body">
						<label for="jb-log-tail" class="screen-reader-text"><?php esc_html_e( 'Log output', 'jardin-toasts' ); ?></label>
						<textarea id="jb-log-tail" readonly rows="12" class="large-text code jb-log-tail"><?php echo esc_textarea( JB_Logger::tail_today( 300 ) ); ?></textarea>
						<p class="description">
							<?php
							$dir = jb_get_log_directory();
							echo esc_html(
								$dir
									? sprintf(
										/* translators: %s: directory path */
										__( 'Log directory: %s', 'jardin-toasts' ),
										$dir
									)
									: __( 'Log directory could not be created.', 'jardin-toasts' )
							);
							?>
						</p>
					</div>
				</div>

			</div>

		</div>

		<div class="jb-settings-footer">
			<?php submit_button( __( 'Save changes', 'jardin-toasts' ), 'primary large', 'submit', false ); ?>
		</div>
	</form>
</div>
