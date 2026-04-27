<?php
/**
 * Tabbed settings page markup.
 *
 * @package BeerJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=beer-journal' );
$tabs     = array(
	'sync'     => __( 'Synchronization', 'beer-journal' ),
	'import'   => __( 'Historical import', 'beer-journal' ),
	'general'  => __( 'Display & content', 'beer-journal' ),
	'rating'   => __( 'Ratings', 'beer-journal' ),
	'advanced' => __( 'Advanced', 'beer-journal' ),
);

$tab_intros = array(
	'sync'     => __( 'Connect your Untappd RSS feed and let WordPress pull new check-ins on a calm schedule. RSS only lists your latest items; full details still come from scraping.', 'beer-journal' ),
	'import'   => __( 'Backfill older check-ins by crawling your public profile. Use small batches and delays to stay polite to Untappd’s servers.', 'beer-journal' ),
	'general'  => __( 'Control how archives look and what gets stored with each check-in on the front of your site.', 'beer-journal' ),
	'rating'   => __( 'Untappd stores fractional ratings. Map them to whole stars for display using the rules below (or override with code).', 'beer-journal' ),
	'advanced' => __( 'Fine-tune scraping pace, structured data, notifications, and troubleshooting logs.', 'beer-journal' ),
);
?>
<div class="wrap bj-admin-wrap">
	<div class="bj-settings-hero">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p class="bj-settings-hero__lead"><?php esc_html_e( 'Untappd → WordPress: sync, import, and present your beer journal with theme-friendly templates.', 'beer-journal' ); ?></p>
	</div>

	<nav class="nav-tab-wrapper bj-nav-tabs" aria-label="<?php esc_attr_e( 'Beer Journal settings sections', 'beer-journal' ); ?>">
		<?php foreach ( $tabs as $id => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $id, $base_url ) ); ?>" class="nav-tab <?php echo $tab === $id ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="options.php" class="bj-settings-form">
		<?php settings_fields( BJ_Settings::OPTION_GROUP ); ?>

		<div class="bj-tab-panel">
			<p class="bj-tab-intro"><?php echo isset( $tab_intros[ $tab ] ) ? esc_html( $tab_intros[ $tab ] ) : ''; ?></p>

			<?php
			/*
			 * Render every settings tab in the same form so options.php receives all keys on save.
			 * (Otherwise saving e.g. "Display & content" would POST missing fields and WordPress clears them.)
			 */
			?>
			<div class="bj-tab-panel-body"<?php echo 'sync' !== $tab ? ' hidden' : ''; ?> data-bj-tab="sync">
				<?php include BJ_PLUGIN_DIR . 'admin/views/stats-box.php'; ?>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'RSS feed & scheduling', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'The feed URL is pre-filled with a working example. Replace it with your own from Untappd → Account if you use a different profile.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="bj_rss_feed_url"><?php esc_html_e( 'Untappd RSS feed URL', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_rss_feed_url" id="bj_rss_feed_url" type="url" class="large-text code" value="<?php echo esc_attr( bj_get_rss_feed_url() ); ?>" autocomplete="off" />
									<p class="description"><?php esc_html_e( 'Public RSS (about 25 recent check-ins). Used by automatic sync and “Run sync now”.', 'beer-journal' ); ?></p>
									<p class="description"><?php echo wp_kses_post( sprintf( /* translators: %s: link to Import tab */ __( 'Historical import uses the same Untappd profile. Set your username on the <a href="%s">Import</a> tab (it should match this feed).', 'beer-journal' ), esc_url( add_query_arg( 'tab', 'import', $base_url ) ) . '#bj-import-profile' ) ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Automatic sync', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_sync_enabled" value="0" />
										<input name="bj_sync_enabled" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_sync_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Enable scheduled RSS synchronization', 'beer-journal' ); ?></span>
									</label>
									<p class="description"><?php
									if ( bj_using_action_scheduler() ) {
										esc_html_e( 'Uses Action Scheduler with adaptive intervals: more frequent when you check in often, lighter when you are quiet. Inspect the beer-journal group under Tools → Scheduled Actions (menu location may differ if WooCommerce owns Action Scheduler).', 'beer-journal' );
									} else {
										esc_html_e( 'Uses adaptive WP-Cron: more frequent when you check in often, lighter when you are quiet. Low-traffic sites should hit wp-cron.php from a real system cron, or install the Action Scheduler plugin for a proper job queue.', 'beer-journal' );
									}
									?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel bj-panel--actions">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Run a sync now', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Fetches the RSS feed immediately and imports any new items (same code path as automatic scheduled sync).', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body bj-panel__body--inline">
						<button type="button" class="button button-secondary" id="bj-sync-now"><?php esc_html_e( 'Run sync now', 'beer-journal' ); ?></button>
						<span class="bj-ajax-status" id="bj-sync-status" aria-live="polite"></span>
					</div>
				</div>

			</div>
			<div class="bj-tab-panel-body"<?php echo 'import' !== $tab ? ' hidden' : ''; ?> data-bj-tab="import">
				<?php
				$rss_username = bj_parse_username_from_rss_url( bj_get_rss_feed_url() );
				$batch_current  = (int) BJ_Settings::get( 'bj_import_batch_size' );
				$delay_current  = (int) BJ_Settings::get( 'bj_import_delay' );
				$batch_choices  = array(
					10  => __( '10 check-ins — small steps', 'beer-journal' ),
					15  => __( '15 check-ins — light', 'beer-journal' ),
					25  => __( '25 check-ins — balanced (recommended)', 'beer-journal' ),
					40  => __( '40 check-ins — fewer clicks', 'beer-journal' ),
					50  => __( '50 check-ins — large (may time out)', 'beer-journal' ),
				);
				$delay_choices  = array(
					0 => __( 'No pause (fast hosts only)', 'beer-journal' ),
					1 => __( '1 second between requests', 'beer-journal' ),
					2 => __( '2 seconds — gentle', 'beer-journal' ),
					3 => __( '3 seconds — polite (default)', 'beer-journal' ),
					5 => __( '5 seconds — very safe', 'beer-journal' ),
					8 => __( '8 seconds — slowest', 'beer-journal' ),
				);
				if ( ! array_key_exists( $batch_current, $batch_choices ) ) {
					$batch_choices[ $batch_current ] = sprintf(
						/* translators: %d: number of check-ins */
						__( '%d check-ins (current)', 'beer-journal' ),
						$batch_current
					);
					ksort( $batch_choices, SORT_NUMERIC );
				}
				if ( ! array_key_exists( $delay_current, $delay_choices ) ) {
					$delay_choices[ $delay_current ] = sprintf(
						/* translators: %d: seconds */
						__( '%d seconds (current)', 'beer-journal' ),
						$delay_current
					);
					ksort( $delay_choices, SORT_NUMERIC );
				}
				?>

				<div class="bj-panel" id="bj-import-profile">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Profile & batching', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Discovery loads your public profile HTML and collects check-in links. Only the username is needed — the same person as in your RSS feed on the Synchronization tab.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="bj_untappd_username"><?php esc_html_e( 'Untappd username', 'beer-journal' ); ?></label></th>
								<td>
									<p class="bj-field-row">
										<input name="bj_untappd_username" id="bj_untappd_username" type="text" class="regular-text" value="<?php echo esc_attr( bj_get_untappd_username() ); ?>" autocomplete="username" spellcheck="false" />
										<?php if ( '' !== $rss_username ) : ?>
											<button type="button" class="button button-secondary" id="bj-use-rss-username"><?php esc_html_e( 'Use username from RSS feed', 'beer-journal' ); ?></button>
										<?php endif; ?>
									</p>
									<p class="description"><?php esc_html_e( 'Enter the profile slug only (e.g. jaz_on), not the full URL. It must match the user segment in your RSS URL: untappd.com/rss/user/…', 'beer-journal' ); ?></p>
									<?php if ( '' !== $rss_username ) : ?>
										<p class="description"><?php echo esc_html( sprintf( /* translators: %s: username */ __( 'Detected from your saved RSS URL: %s', 'beer-journal' ), $rss_username ) ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_import_batch_size"><?php esc_html_e( 'Batch size', 'beer-journal' ); ?></label></th>
								<td>
									<select name="bj_import_batch_size" id="bj_import_batch_size">
										<?php foreach ( $batch_choices as $val => $label ) : ?>
											<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( $batch_current, $val ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'How many check-ins to import each time you click “Import next batch”. Smaller batches finish faster per request; larger ones mean fewer clicks for big backfills.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_import_delay"><?php esc_html_e( 'Pause between requests', 'beer-journal' ); ?></label></th>
								<td>
									<select name="bj_import_delay" id="bj_import_delay">
										<?php foreach ( $delay_choices as $val => $label ) : ?>
											<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( $delay_current, $val ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Wait time after each HTTP request during discovery and import. Longer pauses are kinder to Untappd and your host; shorter pauses finish sooner.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_import_mode"><?php esc_html_e( 'Import mode', 'beer-journal' ); ?></label></th>
								<td>
									<select name="bj_import_mode" id="bj_import_mode">
										<option value="manual" <?php selected( BJ_Settings::get( 'bj_import_mode' ), 'manual' ); ?>><?php esc_html_e( 'Manual — AJAX batches (recommended)', 'beer-journal' ); ?></option>
										<option value="background" <?php selected( BJ_Settings::get( 'bj_import_mode' ), 'background' ); ?>><?php echo esc_html( bj_using_action_scheduler() ? __( 'Background — Action Scheduler', 'beer-journal' ) : __( 'Background — WP-Cron', 'beer-journal' ) ); ?></option>
									</select>
									<p class="description"><?php
									if ( bj_using_action_scheduler() ) {
										esc_html_e( 'Manual keeps you in control; background runs import batches via Action Scheduler when the queue has work.', 'beer-journal' );
									} else {
										esc_html_e( 'Manual keeps you in control; background spreads work across WP-Cron single events when the queue has work (less reliable on low-traffic sites).', 'beer-journal' );
									}
									?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel bj-panel--actions">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Discovery & import', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Run discovery once to build a queue, then import batches until the queue is empty.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body bj-panel__body--inline">
						<button type="button" class="button" id="bj-discover"><?php esc_html_e( 'Discover check-ins', 'beer-journal' ); ?></button>
						<button type="button" class="button button-primary" id="bj-import-batch"><?php esc_html_e( 'Import next batch', 'beer-journal' ); ?></button>
						<span class="bj-ajax-status" id="bj-import-status" aria-live="polite"></span>
					</div>
				</div>
				<input type="hidden" id="bj-discover-max-pages" value="15" />

			</div>
			<div class="bj-tab-panel-body"<?php echo 'general' !== $tab ? ' hidden' : ''; ?> data-bj-tab="general">

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Archives & layout', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Choose how lists of check-ins appear on the main journal and taxonomy pages.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="bj_archive_layout"><?php esc_html_e( 'Archive layout', 'beer-journal' ); ?></label></th>
								<td>
									<select name="bj_archive_layout" id="bj_archive_layout">
										<option value="grid" <?php selected( BJ_Settings::get( 'bj_archive_layout' ), 'grid' ); ?>><?php esc_html_e( 'Grid — cards with photos', 'beer-journal' ); ?></option>
										<option value="table" <?php selected( BJ_Settings::get( 'bj_archive_layout' ), 'table' ); ?>><?php esc_html_e( 'Table — compact rows', 'beer-journal' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Applies to the beer check-in archive and beer style / brewery / venue listings.', 'beer-journal' ); ?></p>
									<?php
									$archive_url = bj_get_checkin_archive_url();
									?>
									<p class="description bj-archive-url">
										<?php echo esc_html__( 'Public journal URL:', 'beer-journal' ); ?>
										<a href="<?php echo esc_url( $archive_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $archive_url ); ?></a>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Imported content', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Control media and taxonomy data saved with each check-in. Check-in notes are stored as post content with automatic paragraphs for plain text.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Beer photos', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_import_images" value="0" />
										<input name="bj_import_images" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_import_images' ) ); ?> />
										<span><?php esc_html_e( 'Yes — import beer photos into the Media Library', 'beer-journal' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default for new installs. Turn off if you only want text.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Venues', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_import_venues" value="0" />
										<input name="bj_import_venues" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_import_venues' ) ); ?> />
										<span><?php esc_html_e( 'Yes — create venue taxonomy terms when a location is present', 'beer-journal' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Lets you browse check-ins by place.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Social stats', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_import_social_data" value="0" />
										<input name="bj_import_social_data" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_import_social_data' ) ); ?> />
										<span><?php esc_html_e( 'Yes — store toast counts when Untappd exposes them', 'beer-journal' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Optional engagement metadata.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Fallback image', 'beer-journal' ); ?></th>
								<td>
									<?php
									$bj_use_ph = BJ_Settings::get( 'bj_use_placeholder_image' );
									?>
									<label class="bj-toggle">
										<input type="hidden" name="bj_use_placeholder_image" value="0" />
										<input name="bj_use_placeholder_image" type="checkbox" value="1" id="bj_use_placeholder_image" <?php checked( $bj_use_ph ); ?> />
										<span><?php esc_html_e( 'Use a fallback image when Untappd download fails (on by default). Uncheck to leave the post without a featured image.', 'beer-journal' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'Pick a generic beer or logo from your Media Library (no attachment ID to type). There is no built-in remote beer-photo API (keys, licensing, reliability); you can wire Open Food Facts, Wikimedia, or your own source in PHP with the bj_placeholder_attachment_id filter, returning a Media Library attachment ID you created.', 'beer-journal' ); ?></p>
									<div class="bj-placeholder-picker" id="bj-placeholder-picker" style="<?php echo $bj_use_ph ? '' : 'display:none;'; ?>">
										<input type="hidden" name="bj_placeholder_image_id" id="bj_placeholder_image_id" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_placeholder_image_id' ) ); ?>" />
										<p class="bj-placeholder-picker__actions">
											<button type="button" class="button" id="bj-placeholder-select"><?php esc_html_e( 'Choose image', 'beer-journal' ); ?></button>
											<button type="button" class="button-link" id="bj-placeholder-clear"><?php esc_html_e( 'Remove', 'beer-journal' ); ?></button>
										</p>
										<div class="bj-placeholder-picker__preview" id="bj-placeholder-preview" aria-live="polite"></div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>

			</div>
			<div class="bj-tab-panel-body"<?php echo 'rating' !== $tab ? ' hidden' : ''; ?> data-bj-tab="rating">
				<?php
				$rule_template = bj_get_default_rating_rules();
				$rules         = BJ_Settings::get( 'bj_rating_rules' );
				if ( ! is_array( $rules ) ) {
					$rules = $rule_template;
				}
				$rules = array_values( $rules );
				while ( count( $rules ) < count( $rule_template ) ) {
					$rules[] = $rule_template[ count( $rules ) ];
				}
				$rules = array_slice( $rules, 0, count( $rule_template ) );

				$default_labels = bj_get_default_rating_labels();
				$labels         = BJ_Settings::get( 'bj_rating_labels' );
				if ( ! is_array( $labels ) ) {
					$labels = $default_labels;
				}
				for ( $li = 0; $li <= 5; $li++ ) {
					if ( ! isset( $labels[ $li ] ) ) {
						$labels[ $li ] = isset( $default_labels[ $li ] ) ? $default_labels[ $li ] : '';
					}
				}
				?>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Star mapping', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Map Untappd’s 0–5 raw score into whole stars. Rules are tested in order; the first matching min/max band wins. The bj_rating_rules filter can still override in code.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Use mapping rules', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_rating_rounding_enabled" value="0" />
										<input name="bj_rating_rounding_enabled" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_rating_rounding_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Map fractional Untappd ratings to whole stars', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
						</table>

						<div class="bj-rating-edit">
							<h3><?php esc_html_e( 'Raw score bands', 'beer-journal' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Each row is one band: if the raw rating is between min and max (inclusive), it maps to the chosen star level.', 'beer-journal' ); ?></p>
							<table class="widefat striped bj-rating-edit-table">
								<thead>
									<tr>
										<th scope="col" class="bj-rating-edit-table__n"><?php esc_html_e( '#', 'beer-journal' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Min (raw)', 'beer-journal' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Max (raw)', 'beer-journal' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Star level', 'beer-journal' ); ?></th>
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
											<td class="bj-rating-edit-table__n"><?php echo esc_html( (string) ( $i + 1 ) ); ?></td>
											<td>
												<label class="screen-reader-text" for="bj-rating-min-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d min', 'beer-journal' ), $i + 1 ) ); ?></label>
												<input name="bj_rating_rules[<?php echo esc_attr( (string) $i ); ?>][min]" id="bj-rating-min-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmin ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="bj-rating-max-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d max', 'beer-journal' ), $i + 1 ) ); ?></label>
												<input name="bj_rating_rules[<?php echo esc_attr( (string) $i ); ?>][max]" id="bj-rating-max-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmax ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="bj-rating-round-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d stars', 'beer-journal' ), $i + 1 ) ); ?></label>
												<select name="bj_rating_rules[<?php echo esc_attr( (string) $i ); ?>][round]" id="bj-rating-round-<?php echo esc_attr( (string) $i ); ?>">
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

							<h3><?php esc_html_e( 'Labels per star level', 'beer-journal' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Optional text for each rounded level (for themes, blocks, or future UI).', 'beer-journal' ); ?></p>
							<table class="widefat striped bj-rating-edit-table bj-rating-edit-table--labels" role="presentation">
								<tbody>
									<?php
									for ( $s = 0; $s <= 5; $s++ ) {
										$lid = 'bj-rating-label-' . $s;
										?>
										<tr>
											<th scope="row">
												<label for="<?php echo esc_attr( $lid ); ?>"><?php echo esc_html( sprintf( /* translators: %d: star count 0-5 */ __( '%d stars', 'beer-journal' ), $s ) ); ?></label>
											</th>
											<td>
												<input name="bj_rating_labels[<?php echo esc_attr( (string) $s ); ?>]" id="<?php echo esc_attr( $lid ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( (string) $labels[ $s ] ); ?>" />
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
			<div class="bj-tab-panel-body"<?php echo 'advanced' !== $tab ? ' hidden' : ''; ?> data-bj-tab="advanced">

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Scraping & performance', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'These settings throttle how aggressively the plugin hits Untappd when it downloads check-in HTML (automatic RSS sync, “Run sync now”, and background import). They are separate from the Synchronization tab (feed URL and whether RSS runs) and from Historical import → “Pause between requests”, which only spaces out clicks in that admin wizard.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="bj_scraping_delay"><?php esc_html_e( 'Scraping delay', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_scraping_delay" id="bj_scraping_delay" type="number" min="1" class="small-text" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_scraping_delay' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'seconds', 'beer-journal' ); ?></span>
									<p class="description"><?php esc_html_e( 'Minimum 1 second (values below 1 are saved as 1). Higher values are gentler; lower values are faster but easier to rate-limit.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_rss_max_per_run"><?php esc_html_e( 'RSS imports per scheduled sync', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_rss_max_per_run" id="bj_rss_max_per_run" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_rss_max_per_run' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Minimum 1 per run (values below 1 are saved as 1). Each automatic sync scrapes at most this many new check-ins; the rest stay in a queue and are processed by follow-up events. “Run sync now” uses a higher limit.', 'beer-journal' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Sync health', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Quick signals when diagnosing RSS backlog or failed scrapes.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<ul class="bj-sync-health-list">
							<li>
								<strong><?php esc_html_e( 'RSS queue depth', 'beer-journal' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( count( bj_get_rss_sync_queue() ) ) ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Draft check-ins needing data', 'beer-journal' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( bj_count_draft_incomplete_checkins() ) ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Scraper markup version', 'beer-journal' ); ?>:</strong>
								<?php echo esc_html( (string) (int) BJ_Scraper_Config::MARKUP_VERSION ); ?>
							</li>
						</ul>
						<p class="description"><?php esc_html_e( 'Recent log lines appear below under “Log file (today)”. Enable debug logging for more detail.', 'beer-journal' ); ?></p>
					</div>
				</div>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'SEO & semantics', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Optional structured data and microformats for themes and parsers.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Schema.org JSON-LD', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_schema_enabled" value="0" />
										<input name="bj_schema_enabled" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_schema_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Output Review structured data on single check-in pages', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Microformats', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_microformats_enabled" value="0" />
										<input name="bj_microformats_enabled" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_microformats_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Add h-entry / e-content classes for IndieWeb-style consumers', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel bj-panel--notice">
					<div class="bj-panel__body">
						<p class="bj-panel__notice-text">
							<span class="dashicons dashicons-info" aria-hidden="true"></span>
							<?php esc_html_e( 'Beer Journal reads public Untappd RSS and HTML only. Untappd may change pages at any time. You are responsible for complying with Untappd’s terms and for content you republish. See docs/legal/scraping-notice.md in the plugin.', 'beer-journal' ); ?>
						</p>
					</div>
				</div>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Email notifications', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Optional alerts when sync completes or fails.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'On successful sync', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_notify_on_sync" value="0" />
										<input name="bj_notify_on_sync" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_notify_on_sync' ) ); ?> />
										<span><?php esc_html_e( 'Email when new check-ins are imported via RSS', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'On RSS error', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_notify_on_error" value="0" />
										<input name="bj_notify_on_error" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_notify_on_error' ) ); ?> />
										<span><?php esc_html_e( 'Email when the feed cannot be fetched', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_notification_email"><?php esc_html_e( 'Notification email', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_notification_email" id="bj_notification_email" type="email" class="regular-text" value="<?php echo esc_attr( (string) BJ_Settings::get( 'bj_notification_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Leave empty to use the site admin email.', 'beer-journal' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Logging & debug', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Verbose logging can help support; keep retention sensible on shared hosting.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Debug logging', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_debug_mode" value="0" />
										<input name="bj_debug_mode" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_debug_mode' ) ); ?> />
										<span><?php esc_html_e( 'Write extra detail to the log file', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_log_retention_days"><?php esc_html_e( 'Log retention', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_log_retention_days" id="bj_log_retention_days" type="number" min="0" class="small-text" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_log_retention_days' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'days (0 = disable rotation hints)', 'beer-journal' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Log file (today)', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Tail of the current log. Paths may differ on your host.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<label for="bj-log-tail" class="screen-reader-text"><?php esc_html_e( 'Log output', 'beer-journal' ); ?></label>
						<textarea id="bj-log-tail" readonly rows="12" class="large-text code bj-log-tail"><?php echo esc_textarea( BJ_Logger::tail_today( 300 ) ); ?></textarea>
						<p class="description">
							<?php
							$dir = bj_get_log_directory();
							echo esc_html(
								$dir
									? sprintf(
										/* translators: %s: directory path */
										__( 'Log directory: %s', 'beer-journal' ),
										$dir
									)
									: __( 'Log directory could not be created.', 'beer-journal' )
							);
							?>
						</p>
					</div>
				</div>

			</div>

		</div>

		<div class="bj-settings-footer">
			<?php submit_button( __( 'Save changes', 'beer-journal' ), 'primary large', 'submit', false ); ?>
		</div>
	</form>
</div>
