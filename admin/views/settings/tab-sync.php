<?php
/**
 * Settings tab: Import & sync (RSS schedule, archive CSV).
 *
 * @package JardinToasts
 *
 * @var string $tab Active tab slug.
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
						<p class="jt-panel__summary"><?php esc_html_e( 'Scheduled sync reads your public Untappd RSS feed and creates check-in posts from feed items only (beer, brewery, venue, date, and image when present). Ratings are usually missing from RSS — those posts stay as drafts until you enrich them from a full data export CSV.', 'jardin-toasts' ); ?></p>
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
									<p class="description"><?php esc_html_e( 'Adaptive timing: more frequent when you check in often, lighter when you are quiet.', 'jardin-toasts' ); ?></p>
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
				</div>

				<div class="jt-panel jt-panel--gdpr-csv">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Full history: data export (CSV)', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Request your personal data from Untappd (privacy / GDPR process) or use an Insider subscription export when available. Import the check-ins CSV here — rows are matched by check-in ID and existing posts are updated. No file is sent with “Save changes”; upload runs only when you click Import.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<p class="jt-panel__inline-actions">
							<input type="file" id="jt-gdpr-csv-file" accept=".csv,text/csv,text/plain" />
							<button type="button" class="button button-secondary" id="jt-import-gdpr-csv"><?php esc_html_e( 'Import check-ins CSV', 'jardin-toasts' ); ?></button>
							<span class="jt-ajax-status jt-ajax-status--block" id="jt-gdpr-csv-status" aria-live="polite"></span>
						</p>
						<p class="description"><?php esc_html_e( 'Expected columns include checkin_id, checkin_url, beer_name, brewery_name, rating_score, created_at, comment, venue_name, serving_type, beer_abv, beer_ibu, photo_url (names may vary). Use filters jt_gdpr_csv_map_row / jt_gdpr_csv_max_rows or jardin_toasts_gdpr_csv_row if your export differs.', 'jardin-toasts' ); ?></p>
					</div>
				</div>

			</div>
