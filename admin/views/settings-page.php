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

			<?php if ( 'sync' === $tab ) : ?>
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
									<p class="description"><?php esc_html_e( 'Uses adaptive WP-Cron: more frequent when you check in often, lighter when you are quiet. Low-traffic sites may need a real cron hitting wp-cron.php.', 'beer-journal' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="bj-panel bj-panel--actions">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Run a sync now', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Fetches the RSS feed immediately and imports any new items (same code path as cron).', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body bj-panel__body--inline">
						<button type="button" class="button button-secondary" id="bj-sync-now"><?php esc_html_e( 'Run sync now', 'beer-journal' ); ?></button>
						<span class="bj-ajax-status" id="bj-sync-status" aria-live="polite"></span>
					</div>
				</div>

			<?php elseif ( 'import' === $tab ) : ?>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Profile & batching', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Discovery scans HTML for check-in links; import pulls each page and creates posts. Defaults are safe for most hosts.', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="bj_untappd_username"><?php esc_html_e( 'Untappd username', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_untappd_username" id="bj_untappd_username" type="text" class="regular-text" value="<?php echo esc_attr( bj_get_untappd_username() ); ?>" autocomplete="username" spellcheck="false" />
									<p class="description"><?php esc_html_e( 'Your public profile slug (from untappd.com/user/…). Pre-filled as an example.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_import_batch_size"><?php esc_html_e( 'Batch size', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_import_batch_size" id="bj_import_batch_size" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_import_batch_size' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Check-ins per “Import next batch” run. Lower if your host is strict on timeouts.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_import_delay"><?php esc_html_e( 'Delay between requests', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_import_delay" id="bj_import_delay" type="number" min="0" class="small-text" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_import_delay' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'seconds', 'beer-journal' ); ?></span>
									<p class="description"><?php esc_html_e( 'Pause between HTTP requests during discovery/import to reduce load on Untappd.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="bj_import_mode"><?php esc_html_e( 'Import mode', 'beer-journal' ); ?></label></th>
								<td>
									<select name="bj_import_mode" id="bj_import_mode">
										<option value="manual" <?php selected( BJ_Settings::get( 'bj_import_mode' ), 'manual' ); ?>><?php esc_html_e( 'Manual — AJAX batches (recommended)', 'beer-journal' ); ?></option>
										<option value="background" <?php selected( BJ_Settings::get( 'bj_import_mode' ), 'background' ); ?>><?php esc_html_e( 'Background — WP-Cron', 'beer-journal' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Manual keeps you in control; background can spread work when cron runs reliably.', 'beer-journal' ); ?></p>
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

			<?php elseif ( 'general' === $tab ) : ?>

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
								<th scope="row"><label for="bj_placeholder_image_id"><?php esc_html_e( 'Placeholder image (attachment ID)', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_placeholder_image_id" id="bj_placeholder_image_id" type="number" min="0" class="small-text" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_placeholder_image_id' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Optional. Used when a beer photo cannot be downloaded. Upload in Media Library and paste the numeric ID. Default 0 = none.', 'beer-journal' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Photos', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_import_images" value="0" />
										<input name="bj_import_images" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_import_images' ) ); ?> />
										<span><?php esc_html_e( 'Import beer photos into the Media Library', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Venues', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_import_venues" value="0" />
										<input name="bj_import_venues" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_import_venues' ) ); ?> />
										<span><?php esc_html_e( 'Create venue taxonomy terms when a location is present', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Social stats', 'beer-journal' ); ?></th>
								<td>
									<label class="bj-toggle">
										<input type="hidden" name="bj_import_social_data" value="0" />
										<input name="bj_import_social_data" type="checkbox" value="1" <?php checked( BJ_Settings::get( 'bj_import_social_data' ) ); ?> />
										<span><?php esc_html_e( 'Store toast counts when Untappd exposes them', 'beer-journal' ); ?></span>
									</label>
								</td>
							</tr>
						</table>
					</div>
				</div>

			<?php elseif ( 'rating' === $tab ) : ?>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Star mapping', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'When enabled, raw ratings are bucketed into 0–5 stars using the ranges below. Developers can replace rules with the bj_rating_rules filter.', 'beer-journal' ); ?></p>
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

						<div class="bj-rating-preview">
							<h3><?php esc_html_e( 'Default ranges (reference)', 'beer-journal' ); ?></h3>
							<table class="widefat striped bj-mini-table">
								<thead>
									<tr>
										<th scope="col"><?php esc_html_e( 'Raw range', 'beer-journal' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Stars', 'beer-journal' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Label', 'beer-journal' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$rules  = bj_get_default_rating_rules();
									$labels = bj_get_default_rating_labels();
									foreach ( $rules as $rule ) {
										$r = isset( $rule['round'] ) ? absint( $rule['round'] ) : 0;
										?>
										<tr>
											<td><?php echo esc_html( $rule['min'] . ' – ' . $rule['max'] ); ?></td>
											<td><?php echo esc_html( (string) $r ); ?></td>
											<td><?php echo esc_html( isset( $labels[ $r ] ) ? $labels[ $r ] : '' ); ?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			<?php else : ?>

				<div class="bj-panel">
					<div class="bj-panel__header">
						<h2 class="bj-panel__title"><?php esc_html_e( 'Scraping & performance', 'beer-journal' ); ?></h2>
						<p class="bj-panel__summary"><?php esc_html_e( 'Delay between HTTP requests when fetching check-in HTML (RSS sync and imports).', 'beer-journal' ); ?></p>
					</div>
					<div class="bj-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="bj_scraping_delay"><?php esc_html_e( 'Scraping delay', 'beer-journal' ); ?></label></th>
								<td>
									<input name="bj_scraping_delay" id="bj_scraping_delay" type="number" min="1" class="small-text" value="<?php echo esc_attr( (string) (int) BJ_Settings::get( 'bj_scraping_delay' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'seconds', 'beer-journal' ); ?></span>
									<p class="description"><?php esc_html_e( 'Higher values are gentler; lower values are faster but easier to rate-limit.', 'beer-journal' ); ?></p>
								</td>
							</tr>
						</table>
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

			<?php endif; ?>

		</div>

		<div class="bj-settings-footer">
			<?php submit_button( __( 'Save changes', 'beer-journal' ), 'primary large', 'submit', false ); ?>
		</div>
	</form>
</div>
