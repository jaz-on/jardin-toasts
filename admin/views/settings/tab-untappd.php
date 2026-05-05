<?php
/**
 * Settings tab: Untappd account.
 *
 * @package JardinToasts
 *
 * @var string $tab           Active tab slug.
 * @var string $rss_username Parsed username from RSS URL (may be empty).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
			<div class="jardin-toasts-tab-panel-body"<?php echo 'untappd' !== $tab ? ' hidden' : ''; ?> data-jardin-toasts-tab="untappd">
				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Account', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Use the same RSS URL and username you see on Untappd (Account → RSS). Values are stored in the WordPress options table — only trusted administrators should access this screen.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jardin_toasts_rss_feed_url"><?php esc_html_e( 'Untappd RSS feed URL', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jardin_toasts_rss_feed_url" id="jardin_toasts_rss_feed_url" type="url" class="large-text code" value="<?php echo esc_attr( jardin_toasts_get_rss_feed_url() ); ?>" autocomplete="off" />
									<p class="description"><?php esc_html_e( 'Public RSS (about 25 recent check-ins). Used by automatic sync and “Run sync now” on the Import & sync tab.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jardin_toasts_untappd_username"><?php esc_html_e( 'Untappd username', 'jardin-toasts' ); ?></label></th>
								<td>
									<p class="jardin-toasts-field-row">
										<input name="jardin_toasts_untappd_username" id="jardin_toasts_untappd_username" type="text" class="regular-text" value="<?php echo esc_attr( jardin_toasts_get_untappd_username() ); ?>" autocomplete="username" spellcheck="false" />
										<?php if ( '' !== $rss_username ) : ?>
											<button type="button" class="button button-secondary" id="jardin-toasts-use-rss-username"><?php esc_html_e( 'Use username from RSS feed', 'jardin-toasts' ); ?></button>
										<?php endif; ?>
									</p>
									<p class="description"><?php esc_html_e( 'Profile slug only (e.g. jaz_on), not the full URL. Should match the user segment in your RSS URL (untappd.com/rss/user/…). Optional reference for your records; full history is imported via CSV export.', 'jardin-toasts' ); ?></p>
									<?php if ( '' !== $rss_username ) : ?>
										<p class="description"><?php echo esc_html( sprintf( /* translators: %s: username */ __( 'Detected from your saved RSS URL: %s', 'jardin-toasts' ), $rss_username ) ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jardin-toasts-panel jardin-toasts-panel--actions">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Test connection', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Save your feed URL first, then verify WordPress can read the RSS feed.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body jardin-toasts-panel__body--inline">
						<p class="jardin-toasts-test-row">
							<button type="button" class="button button-secondary" id="jardin-toasts-test-rss"><?php esc_html_e( 'Test RSS feed', 'jardin-toasts' ); ?></button>
							<span id="jardin-toasts-test-rss-result" class="jardin-toasts-test-result" aria-live="polite"></span>
						</p>
					</div>
				</div>

			</div>
