<?php
/**
 * Settings tab: Import & sync (RSS schedule, immediate sync, historical queue).
 *
 * @package JardinToasts
 *
 * @var string $tab            Active tab slug.
 * @var int    $batch_current  Selected batch size.
 * @var int    $delay_current  Selected delay seconds.
 * @var array<int,string> $batch_choices Batch size labels.
 * @var array<int,string> $delay_choices Delay labels.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
			<div class="jt-tab-panel-body"<?php echo 'sync' !== $tab ? ' hidden' : ''; ?> data-jt-tab="sync">
				<?php include JT_PLUGIN_DIR . 'admin/views/stats-box.php'; ?>

				<?php if ( wp_script_is( 'jt-admin-dataviews', 'enqueued' ) ) : ?>
				<div class="jt-panel jt-panel--compact">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Diagnostics (DataViews)', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Read-only snapshot from current options. Requires a built admin bundle (npm run build in the plugin directory).', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<div id="jt-sync-dataviews-root" class="jt-sync-dataviews-mount"></div>
					</div>
				</div>
				<?php endif; ?>

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Background RSS sync', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Scheduled sync fetches your public RSS feed and imports new check-ins (details still come from scraping).', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Scheduled sync', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_sync_enabled" value="0" />
										<input name="jt_sync_enabled" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_sync_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Enable scheduled RSS synchronization', 'jardin-toasts' ); ?></span>
									</label>
									<?php require JT_PLUGIN_DIR . 'admin/views/partials/settings-cron-hint.php'; ?>
									<p class="description"><?php esc_html_e( 'Adaptive timing: more frequent when you check in often, lighter when you are quiet. Historical import batches use the same scheduler when a queue remains after discovery or “Import next batch”.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
					<div class="jt-panel__footer">
						<?php submit_button( __( 'Save changes', 'jardin-toasts' ), 'primary large', 'submit', false ); ?>
					</div>
				</div>

				<div class="jt-sync-actions-grid">
					<div class="jt-panel jt-panel--actions jt-sync-action-panel">
						<div class="jt-panel__header">
							<h2 class="jt-panel__title"><?php esc_html_e( 'Run sync now', 'jardin-toasts' ); ?></h2>
							<p class="jt-panel__summary"><?php esc_html_e( 'Fetches the RSS feed immediately (same code path as scheduled sync).', 'jardin-toasts' ); ?></p>
						</div>
						<div class="jt-panel__body jt-panel__body--inline">
							<button type="button" class="button button-secondary" id="jt-sync-now"><?php esc_html_e( 'Run sync now', 'jardin-toasts' ); ?></button>
							<span class="jt-ajax-status" id="jt-sync-status" aria-live="polite"></span>
						</div>
					</div>

					<div class="jt-panel jt-panel--actions jt-sync-action-panel" id="jt-import-backfill">
						<div class="jt-panel__header">
							<h2 class="jt-panel__title"><?php esc_html_e( 'Historical import', 'jardin-toasts' ); ?></h2>
							<p class="jt-panel__summary"><?php esc_html_e( 'Discovery merges check-in IDs from your profile (anonymous HTML is shallow; optional session cookie on the Account tab enables deep pagination like “Show more”) with your configured Untappd RSS feed. It queues IDs not already stored in WordPress; the queue drains via Action Scheduler when available, otherwise WP-Cron.', 'jardin-toasts' ); ?></p>
						</div>
						<div class="jt-panel__body">
							<p class="jt-panel__inline-actions">
								<button type="button" class="button button-secondary" id="jt-discover"><?php esc_html_e( 'Discover check-ins', 'jardin-toasts' ); ?></button>
								<button type="button" class="button button-secondary" id="jt-import-batch"><?php esc_html_e( 'Import next batch', 'jardin-toasts' ); ?></button>
								<span class="jt-ajax-status jt-ajax-status--block" id="jt-import-status" aria-live="polite"></span>
							</p>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="jt_import_batch_size"><?php esc_html_e( 'Batch size', 'jardin-toasts' ); ?></label></th>
									<td>
										<select name="jt_import_batch_size" id="jt_import_batch_size">
											<?php foreach ( $batch_choices as $val => $label ) : ?>
												<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( $batch_current, $val ); ?>><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
										</select>
										<p class="description"><?php esc_html_e( 'How many check-ins each import step processes. Smaller batches are safer on slow hosts.', 'jardin-toasts' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="jt_import_delay"><?php esc_html_e( 'Pause between requests', 'jardin-toasts' ); ?></label></th>
									<td>
										<select name="jt_import_delay" id="jt_import_delay">
											<?php foreach ( $delay_choices as $val => $label ) : ?>
												<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( $delay_current, $val ); ?>><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
										</select>
										<p class="description"><?php esc_html_e( 'Wait after each HTTP request during discovery and import; also spaces background batches.', 'jardin-toasts' ); ?></p>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<input type="hidden" id="jt-discover-max-pages" value="15" />

				<div class="jt-panel jt-panel--gdpr-csv">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'GDPR / data export (CSV)', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'After Untappd emails your personal data export, download the check-ins CSV and import it here. Rows are matched by check-in ID; existing posts are updated. No file field is posted with “Save changes” — upload runs only when you click Import.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<p class="jt-panel__inline-actions">
							<input type="file" id="jt-gdpr-csv-file" accept=".csv,text/csv,text/plain" />
							<button type="button" class="button button-secondary" id="jt-import-gdpr-csv"><?php esc_html_e( 'Import check-ins CSV', 'jardin-toasts' ); ?></button>
							<span class="jt-ajax-status jt-ajax-status--block" id="jt-gdpr-csv-status" aria-live="polite"></span>
						</p>
						<p class="description"><?php esc_html_e( 'Expected columns include checkin_id, checkin_url, beer_name, brewery_name, rating_score, created_at, comment, venue_name, serving_type, beer_abv, beer_ibu, photo_url (names may vary). Use filters jt_gdpr_csv_map_row / jt_gdpr_csv_max_rows if your export differs.', 'jardin-toasts' ); ?></p>
					</div>
				</div>

			</div>
