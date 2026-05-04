<?php
/**
 * Shared WP-Cron / Action Scheduler context paragraph for RSS settings.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="description jt-cron-hint">
	<?php if ( jt_using_action_scheduler() ) : ?>
		<span class="dashicons dashicons-yes-alt" style="color:#00a32a;font-size:1em;vertical-align:text-bottom;" aria-hidden="true"></span>
		<?php esc_html_e( 'Action Scheduler detected — RSS sync runs reliably in the background. Inspect scheduled actions under Tools → Scheduled Actions, group “jardin-toasts” (the menu label may differ if WooCommerce owns Action Scheduler).', 'jardin-toasts' ); ?>
	<?php elseif ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
		<span class="dashicons dashicons-yes-alt" style="color:#00a32a;font-size:1em;vertical-align:text-bottom;" aria-hidden="true"></span>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %s: link to Action Scheduler */
				__( 'WP-Cron with <code>DISABLE_WP_CRON</code> enabled — a system task is triggering WordPress, which is the recommended setup. For even more reliable scheduling you can also install %s.', 'jardin-toasts' ),
				'<a href="https://actionscheduler.org/" target="_blank" rel="noopener noreferrer">Action Scheduler</a>'
			)
		);
		?>
	<?php else : ?>
		<span class="dashicons dashicons-warning" style="color:#dba617;font-size:1em;vertical-align:text-bottom;" aria-hidden="true"></span>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: 1: Action Scheduler link 2: wp-config snippet */
				__( 'WP-Cron is driven by site traffic — jobs may be delayed on low-traffic hosts. Install %1$s for a proper queue, or add %2$s to <code>wp-config.php</code> and call <code>wp-cron.php</code> from a real system cron.', 'jardin-toasts' ),
				'<a href="https://actionscheduler.org/" target="_blank" rel="noopener noreferrer">Action Scheduler</a>',
				"<code>define( 'DISABLE_WP_CRON', true );</code>"
			)
		);
		?>
	<?php endif; ?>
</p>
