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
			<div class="jt-tab-panel-body"<?php echo 'untappd' !== $tab ? ' hidden' : ''; ?> data-jt-tab="untappd">
				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Account', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Use the same RSS URL and username you see on Untappd (Account → RSS). Values are stored in the WordPress options table — only trusted administrators should access this screen.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jt_rss_feed_url"><?php esc_html_e( 'Untappd RSS feed URL', 'jardin-toasts' ); ?></label></th>
								<td>
									<input name="jt_rss_feed_url" id="jt_rss_feed_url" type="url" class="large-text code" value="<?php echo esc_attr( jt_get_rss_feed_url() ); ?>" autocomplete="off" />
									<p class="description"><?php esc_html_e( 'Public RSS (about 25 recent check-ins). Used by automatic sync and “Run sync now” on the Import & sync tab.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jt_untappd_username"><?php esc_html_e( 'Untappd username', 'jardin-toasts' ); ?></label></th>
								<td>
									<p class="jt-field-row">
										<input name="jt_untappd_username" id="jt_untappd_username" type="text" class="regular-text" value="<?php echo esc_attr( jt_get_untappd_username() ); ?>" autocomplete="username" spellcheck="false" />
										<?php if ( '' !== $rss_username ) : ?>
											<button type="button" class="button button-secondary" id="jt-use-rss-username"><?php esc_html_e( 'Use username from RSS feed', 'jardin-toasts' ); ?></button>
										<?php endif; ?>
									</p>
									<p class="description"><?php esc_html_e( 'Profile slug only (e.g. jaz_on), not the full URL. Used for historical discovery and scraping; it should match the user segment in your RSS URL (untappd.com/rss/user/…).', 'jardin-toasts' ); ?></p>
									<?php if ( '' !== $rss_username ) : ?>
										<p class="description"><?php echo esc_html( sprintf( /* translators: %s: username */ __( 'Detected from your saved RSS URL: %s', 'jardin-toasts' ), $rss_username ) ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="jt_untappd_session_cookie"><?php esc_html_e( 'Session cookie (optional)', 'jardin-toasts' ); ?></label></th>
								<td>
									<textarea name="jt_untappd_session_cookie" id="jt_untappd_session_cookie" class="large-text code" rows="3" autocomplete="off" spellcheck="false" placeholder="<?php esc_attr_e( 'name=value; name2=value2 …', 'jardin-toasts' ); ?>"><?php echo esc_textarea( (string) JT_Settings::get( 'jt_untappd_session_cookie' ) ); ?></textarea>
									<p class="description"><?php esc_html_e( 'While logged in on untappd.com in your browser, copy the full Cookie header (or all session cookies concatenated with “; ”) from DevTools → Network → any document request to untappd.com. When this field is non-empty, historical discovery uses the same “Show more” pagination as the site (/profile/more_feed/…) instead of anonymous HTML only. Treat this like a password: anyone with it can act as you on Untappd until it expires.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jt-panel jt-panel--actions">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Test connections', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Save your feed and username first, then run each test to confirm WordPress can reach Untappd over RSS and over the profile HTML (anonymous, or with your session cookie if you saved one).', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body jt-panel__body--inline">
						<p class="jt-test-row">
							<button type="button" class="button button-secondary" id="jt-test-rss"><?php esc_html_e( 'Test RSS feed', 'jardin-toasts' ); ?></button>
							<span id="jt-test-rss-result" class="jt-test-result" aria-live="polite"></span>
						</p>
						<p class="jt-test-row">
							<button type="button" class="button button-secondary" id="jt-test-profile"><?php esc_html_e( 'Test public profile', 'jardin-toasts' ); ?></button>
							<span id="jt-test-profile-result" class="jt-test-result" aria-live="polite"></span>
						</p>
					</div>
				</div>

			</div>
