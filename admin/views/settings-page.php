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
	'general'  => __( 'General', 'beer-journal' ),
	'rating'   => __( 'Rating', 'beer-journal' ),
	'advanced' => __( 'Advanced', 'beer-journal' ),
);
?>
<div class="wrap bj-admin-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $id => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $id, $base_url ) ); ?>" class="nav-tab <?php echo $tab === $id ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</h2>

	<form method="post" action="options.php" class="bj-settings-form">
		<?php settings_fields( BJ_Settings::OPTION_GROUP ); ?>

		<?php if ( 'sync' === $tab ) : ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="bj_rss_feed_url"><?php esc_html_e( 'Untappd RSS feed URL', 'beer-journal' ); ?></label></th>
					<td>
						<input name="bj_rss_feed_url" id="bj_rss_feed_url" type="url" class="regular-text" value="<?php echo esc_attr( get_option( 'bj_rss_feed_url', '' ) ); ?>" placeholder="https://untappd.com/rss/user/…" />
						<p class="description"><?php esc_html_e( 'Your public RSS feed (about 25 most recent check-ins).', 'beer-journal' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Automatic sync', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_sync_enabled" value="0" />
							<input name="bj_sync_enabled" type="checkbox" value="1" <?php checked( get_option( 'bj_sync_enabled', true ) ); ?> />
							<?php esc_html_e( 'Enable scheduled RSS synchronization (adaptive interval).', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<p>
				<button type="button" class="button button-secondary" id="bj-sync-now"><?php esc_html_e( 'Run sync now', 'beer-journal' ); ?></button>
				<span class="bj-ajax-status" id="bj-sync-status" aria-live="polite"></span>
			</p>
		<?php elseif ( 'import' === $tab ) : ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="bj_untappd_username"><?php esc_html_e( 'Untappd username', 'beer-journal' ); ?></label></th>
					<td>
						<input name="bj_untappd_username" id="bj_untappd_username" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'bj_untappd_username', '' ) ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bj_import_batch_size"><?php esc_html_e( 'Batch size', 'beer-journal' ); ?></label></th>
					<td>
						<input name="bj_import_batch_size" id="bj_import_batch_size" type="number" min="1" max="100" value="<?php echo esc_attr( (string) get_option( 'bj_import_batch_size', 25 ) ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bj_import_delay"><?php esc_html_e( 'Delay between requests (seconds)', 'beer-journal' ); ?></label></th>
					<td>
						<input name="bj_import_delay" id="bj_import_delay" type="number" min="0" value="<?php echo esc_attr( (string) get_option( 'bj_import_delay', 3 ) ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Import mode', 'beer-journal' ); ?></th>
					<td>
						<select name="bj_import_mode" id="bj_import_mode">
							<option value="manual" <?php selected( get_option( 'bj_import_mode', 'manual' ), 'manual' ); ?>><?php esc_html_e( 'Manual (AJAX batches)', 'beer-journal' ); ?></option>
							<option value="background" <?php selected( get_option( 'bj_import_mode', 'manual' ), 'background' ); ?>><?php esc_html_e( 'Background (WP-Cron)', 'beer-journal' ); ?></option>
						</select>
					</td>
				</tr>
			</table>
			<p>
				<button type="button" class="button" id="bj-discover"><?php esc_html_e( 'Discover check-ins', 'beer-journal' ); ?></button>
				<button type="button" class="button button-primary" id="bj-import-batch"><?php esc_html_e( 'Import next batch', 'beer-journal' ); ?></button>
				<span class="bj-ajax-status" id="bj-import-status" aria-live="polite"></span>
			</p>
			<p class="description"><?php esc_html_e( 'Discovery scans your profile HTML for check-in links; import runs in batches to respect rate limits.', 'beer-journal' ); ?></p>
		<?php elseif ( 'general' === $tab ) : ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Images', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_import_images" value="0" />
							<input name="bj_import_images" type="checkbox" value="1" <?php checked( get_option( 'bj_import_images', true ) ); ?> />
							<?php esc_html_e( 'Import beer photos into the Media Library', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Venues', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_import_venues" value="0" />
							<input name="bj_import_venues" type="checkbox" value="1" <?php checked( get_option( 'bj_import_venues', true ) ); ?> />
							<?php esc_html_e( 'Create venue taxonomy terms', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Social data', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_import_social_data" value="0" />
							<input name="bj_import_social_data" type="checkbox" value="1" <?php checked( get_option( 'bj_import_social_data', true ) ); ?> />
							<?php esc_html_e( 'Import toast counts when available', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
			</table>
		<?php elseif ( 'rating' === $tab ) : ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Rounding', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_rating_rounding_enabled" value="0" />
							<input name="bj_rating_rounding_enabled" type="checkbox" value="1" <?php checked( get_option( 'bj_rating_rounding_enabled', true ) ); ?> />
							<?php esc_html_e( 'Map raw Untappd ratings to 0–5 stars using rules below.', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<p class="description"><?php esc_html_e( 'Default mapping rules apply unless you filter bj_rating_rules in code.', 'beer-journal' ); ?></p>
		<?php else : ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Scraping delay (seconds)', 'beer-journal' ); ?></th>
					<td>
						<input name="bj_scraping_delay" type="number" min="1" value="<?php echo esc_attr( (string) get_option( 'bj_scraping_delay', 3 ) ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Schema.org JSON-LD', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_schema_enabled" value="0" />
							<input name="bj_schema_enabled" type="checkbox" value="1" <?php checked( get_option( 'bj_schema_enabled', true ) ); ?> />
							<?php esc_html_e( 'Output structured data on check-in pages', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Microformats', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_microformats_enabled" value="0" />
							<input name="bj_microformats_enabled" type="checkbox" value="1" <?php checked( get_option( 'bj_microformats_enabled', true ) ); ?> />
							<?php esc_html_e( 'Add h-entry / e-content classes in templates', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Debug logging', 'beer-journal' ); ?></th>
					<td>
						<label>
							<input type="hidden" name="bj_debug_mode" value="0" />
							<input name="bj_debug_mode" type="checkbox" value="1" <?php checked( get_option( 'bj_debug_mode', false ) ); ?> />
							<?php esc_html_e( 'Verbose logs (may include more detail)', 'beer-journal' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Log retention (days)', 'beer-journal' ); ?></th>
					<td>
						<input name="bj_log_retention_days" type="number" min="0" value="<?php echo esc_attr( (string) get_option( 'bj_log_retention_days', 30 ) ); ?>" />
					</td>
				</tr>
			</table>
			<h2><?php esc_html_e( 'Logs (today)', 'beer-journal' ); ?></h2>
			<textarea readonly rows="12" class="large-text code"><?php echo esc_textarea( BJ_Logger::tail_today( 300 ) ); ?></textarea>
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
		<?php endif; ?>

		<?php submit_button(); ?>
	</form>

	<?php if ( 'import' === $tab ) : ?>
		<input type="hidden" id="bj-discover-max-pages" value="15" />
	<?php endif; ?>
</div>
