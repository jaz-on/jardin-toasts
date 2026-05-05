<?php
/**
 * Settings tab: Advanced (RSS limits, SEO, logs).
 *
 * @package JardinToasts
 *
 * @var string $tab Active tab slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
			<div class="jardin-toasts-tab-panel-body"<?php echo 'advanced' !== $tab ? ' hidden' : ''; ?> data-jardin-toasts-tab="advanced">

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'RSS sync limits', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Caps how many new feed items each scheduled run turns into posts. Extra items stay in a queue and are processed by follow-up cron events.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jardin_toasts_rss_max_per_run"><?php esc_html_e( 'RSS imports per scheduled sync', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jardin_toasts_rss_max_per_run" id="jardin_toasts_rss_max_per_run" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr( (string) (int) Jardin_Toasts_Settings::get( 'jardin_toasts_rss_max_per_run' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Minimum 1 per run (values below 1 are saved as 1). “Run sync now” uses a higher limit.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Sync health', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Quick signals when diagnosing RSS backlog or incomplete drafts (often RSS items without a rating).', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<ul class="jardin-toasts-sync-health-list">
							<li>
								<strong><?php esc_html_e( 'RSS queue depth', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( count( jardin_toasts_get_rss_sync_queue() ) ) ); ?>
							</li>
							<li>
								<strong><?php esc_html_e( 'Draft check-ins needing data', 'jardin-toasts' ); ?>:</strong>
								<?php echo esc_html( number_format_i18n( jardin_toasts_count_draft_incomplete_checkins() ) ); ?>
							</li>
						</ul>
						<p class="description"><?php esc_html_e( 'Recent log lines appear below under “Log file (today)”. Enable debug logging for more detail.', 'jardin-toasts' ); ?></p>
					</div>
				</div>

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'SEO & semantics', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Optional structured data and microformats for themes and parsers.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Schema.org JSON-LD', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_schema_enabled" value="0" />
										<input name="jardin_toasts_schema_enabled" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_schema_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Output Review structured data on single check-in pages', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Microformats', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_microformats_enabled" value="0" />
										<input name="jardin_toasts_microformats_enabled" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_microformats_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Add h-entry / e-content classes for IndieWeb-style consumers', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jardin-toasts-panel jardin-toasts-panel--notice">
					<div class="jardin-toasts-panel__body">
						<p class="jardin-toasts-panel__notice-text">
							<span class="dashicons dashicons-info" aria-hidden="true"></span>
							<?php esc_html_e( 'Jardin Toasts reads your public Untappd RSS feed and imports check-in CSVs you provide from Untappd’s data export. You are responsible for complying with Untappd’s terms and for content you republish.', 'jardin-toasts' ); ?>
						</p>
					</div>
				</div>

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Email notifications', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Optional alerts when sync completes or fails.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'On successful sync', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_notify_on_sync" value="0" />
										<input name="jardin_toasts_notify_on_sync" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_notify_on_sync' ) ); ?> />
										<span><?php esc_html_e( 'Email when new check-ins are imported via RSS', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'On RSS error', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_notify_on_error" value="0" />
										<input name="jardin_toasts_notify_on_error" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_notify_on_error' ) ); ?> />
										<span><?php esc_html_e( 'Email when the feed cannot be fetched', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jardin_toasts_notification_email"><?php esc_html_e( 'Notification email', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jardin_toasts_notification_email" id="jardin_toasts_notification_email" type="email" class="regular-text" value="<?php echo esc_attr( (string) Jardin_Toasts_Settings::get( 'jardin_toasts_notification_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Leave empty to use the site admin email.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Logging & debug', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Verbose logging can help support; keep retention sensible on shared hosting.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Debug logging', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_debug_mode" value="0" />
										<input name="jardin_toasts_debug_mode" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_debug_mode' ) ); ?> />
										<span><?php esc_html_e( 'Write extra detail to the log file', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jardin_toasts_log_retention_days"><?php esc_html_e( 'Log retention', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jardin_toasts_log_retention_days" id="jardin_toasts_log_retention_days" type="number" min="0" class="small-text" value="<?php echo esc_attr( (string) (int) Jardin_Toasts_Settings::get( 'jardin_toasts_log_retention_days' ) ); ?>" />
									<span class="description"><?php esc_html_e( 'days (0 = disable rotation hints)', 'jardin-toasts' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Log file (today)', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Tail of the current log. Paths may differ on your host.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<label for="jardin-toasts-log-tail" class="screen-reader-text"><?php esc_html_e( 'Log output', 'jardin-toasts' ); ?></label>
						<textarea id="jardin-toasts-log-tail" readonly rows="12" class="large-text code jardin-toasts-log-tail"><?php echo esc_textarea( Jardin_Toasts_Logger::tail_today( 300 ) ); ?></textarea>
						<p class="description">
							<?php
							$dir = jardin_toasts_get_log_directory();
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
