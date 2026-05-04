<?php
/**
 * Settings tab: Advanced (scraping, SEO, logs).
 *
 * @package JardinToasts
 *
 * @var string $tab Active tab slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
			<div class="jt-tab-panel-body"<?php echo 'advanced' !== $tab ? ' hidden' : ''; ?> data-jt-tab="advanced">

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Scraping & performance', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'These settings throttle how aggressively the plugin hits Untappd when it downloads check-in HTML (RSS sync, “Run sync now”, and historical import). They are separate from the Untappd account tab (feed URL and username) and from Import & sync → “Pause between requests”, which spaces discovery and import requests.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jt_scraping_delay"><?php esc_html_e( 'Scraping delay', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jt_scraping_delay" id="jt_scraping_delay" type="number" min="1" class="small-text" value="<?php echo esc_attr( (string) (int) JT_Settings::get( 'jt_scraping_delay' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'seconds', 'jardin-toasts' ); ?></span>
									<p class="description"><?php esc_html_e( 'Minimum 1 second (values below 1 are saved as 1). Higher values are gentler; lower values are faster but easier to rate-limit.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jt_rss_max_per_run"><?php esc_html_e( 'RSS imports per scheduled sync', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jt_rss_max_per_run" id="jt_rss_max_per_run" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr( (string) (int) JT_Settings::get( 'jt_rss_max_per_run' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Minimum 1 per run (values below 1 are saved as 1). Each automatic sync scrapes at most this many new check-ins; the rest stay in a queue and are processed by follow-up events. “Run sync now” uses a higher limit.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Sync health', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Quick signals when diagnosing RSS backlog or failed scrapes.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<ul class="jt-sync-health-list">
							<li>
								<strong><?php esc_html_e( 'RSS queue depth', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( count( jt_get_rss_sync_queue() ) ) ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Draft check-ins needing data', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( jt_count_draft_incomplete_checkins() ) ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Scraper markup version', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( (string) (int) JT_Scraper_Config::MARKUP_VERSION ); ?>
							</li>
						</ul>
						<p class="description"><?php esc_html_e( 'Recent log lines appear below under “Log file (today)”. Enable debug logging for more detail.', 'jardin-toasts' ); ?></p>
					</div>
				</div>

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'SEO & semantics', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Optional structured data and microformats for themes and parsers.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Schema.org JSON-LD', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_schema_enabled" value="0" />
										<input name="jt_schema_enabled" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_schema_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Output Review structured data on single check-in pages', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Microformats', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_microformats_enabled" value="0" />
										<input name="jt_microformats_enabled" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_microformats_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Add h-entry / e-content classes for IndieWeb-style consumers', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jt-panel jt-panel--notice">
					<div class="jt-panel__body">
						<p class="jt-panel__notice-text">
							<span class="dashicons dashicons-info" aria-hidden="true"></span>
							<?php esc_html_e( 'Jardin Toasts reads public Untappd RSS and HTML only. Untappd may change pages at any time. You are responsible for complying with Untappd’s terms and for content you republish. See docs/legal/scraping-notice.md in the plugin.', 'jardin-toasts' ); ?>
						</p>
					</div>
				</div>

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Email notifications', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Optional alerts when sync completes or fails.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'On successful sync', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_notify_on_sync" value="0" />
										<input name="jt_notify_on_sync" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_notify_on_sync' ) ); ?> />
										<span><?php esc_html_e( 'Email when new check-ins are imported via RSS', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'On RSS error', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_notify_on_error" value="0" />
										<input name="jt_notify_on_error" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_notify_on_error' ) ); ?> />
										<span><?php esc_html_e( 'Email when the feed cannot be fetched', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jt_notification_email"><?php esc_html_e( 'Notification email', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jt_notification_email" id="jt_notification_email" type="email" class="regular-text" value="<?php echo esc_attr( (string) JT_Settings::get( 'jt_notification_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Leave empty to use the site admin email.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Logging & debug', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Verbose logging can help support; keep retention sensible on shared hosting.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Debug logging', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_debug_mode" value="0" />
										<input name="jt_debug_mode" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_debug_mode' ) ); ?> />
										<span><?php esc_html_e( 'Write extra detail to the log file', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jt_log_retention_days"><?php esc_html_e( 'Log retention', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jt_log_retention_days" id="jt_log_retention_days" type="number" min="0" class="small-text" value="<?php echo esc_attr( (string) (int) JT_Settings::get( 'jt_log_retention_days' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'days (0 = disable rotation hints)', 'jardin-toasts' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Log file (today)', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Tail of the current log. Paths may differ on your host.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<label for="jt-log-tail" class="screen-reader-text"><?php esc_html_e( 'Log output', 'jardin-toasts' ); ?></label>
						<textarea id="jt-log-tail" readonly rows="12" class="large-text code jt-log-tail"><?php echo esc_textarea( JT_Logger::tail_today( 300 ) ); ?></textarea>
						<p class="description">
							<?php
							$dir = jt_get_log_directory();
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
