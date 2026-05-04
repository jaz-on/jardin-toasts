<?php
/**
 * Settings tab: Display & content (archives, import options, ratings).
 *
 * @package JardinToasts
 *
 * @var string $tab Active tab slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rule_template = jt_get_default_rating_rules();
$rules         = JT_Settings::get( 'jt_rating_rules' );
if ( ! is_array( $rules ) ) {
	$rules = $rule_template;
}
$rules = array_values( $rules );
while ( count( $rules ) < count( $rule_template ) ) {
	$rules[] = $rule_template[ count( $rules ) ];
}
$rules = array_slice( $rules, 0, count( $rule_template ) );

$default_labels = jt_get_default_rating_labels();
$labels         = JT_Settings::get( 'jt_rating_labels' );
if ( ! is_array( $labels ) ) {
	$labels = $default_labels;
}
for ( $li = 0; $li <= 5; $li++ ) {
	if ( ! isset( $labels[ $li ] ) ) {
		$labels[ $li ] = isset( $default_labels[ $li ] ) ? $default_labels[ $li ] : '';
	}
}
?>
			<div class="jt-tab-panel-body"<?php echo 'display' !== $tab ? ' hidden' : ''; ?> data-jt-tab="display">

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Archives & layout', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Choose how lists of check-ins appear on the main journal and taxonomy pages.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jt_archive_layout"><?php esc_html_e( 'Archive layout', 'jardin-toasts' ); ?></label></th>
								<td>
									<select name="jt_archive_layout" id="jt_archive_layout">
										<option value="grid" <?php selected( JT_Settings::get( 'jt_archive_layout' ), 'grid' ); ?>><?php esc_html_e( 'Grid — cards with photos', 'jardin-toasts' ); ?></option>
										<option value="table" <?php selected( JT_Settings::get( 'jt_archive_layout' ), 'table' ); ?>><?php esc_html_e( 'Table — compact rows', 'jardin-toasts' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Applies to the beer check-in archive and beer style / brewery / venue listings.', 'jardin-toasts' ); ?></p>
									<?php
									$archive_url = jt_get_checkin_archive_url();
									?>
									<p class="description jt-archive-url">
										<?php echo esc_html__( 'Public journal URL:', 'jardin-toasts' ); ?>
										<a href="<?php echo esc_url( $archive_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $archive_url ); ?></a>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jt-panel">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Imported content', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Control media and taxonomy data saved with each check-in. Check-in notes are stored as post content with automatic paragraphs for plain text.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Beer photos', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_import_images" value="0" />
										<input name="jt_import_images" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_import_images' ) ); ?> />
										<span><?php esc_html_e( 'Yes — import beer photos into the Media Library', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default for new installs. Turn off if you only want text.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Venues', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_import_venues" value="0" />
										<input name="jt_import_venues" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_import_venues' ) ); ?> />
										<span><?php esc_html_e( 'Yes — create venue taxonomy terms when a location is present', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Lets you browse check-ins by place.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Social stats', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_import_social_data" value="0" />
										<input name="jt_import_social_data" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_import_social_data' ) ); ?> />
										<span><?php esc_html_e( 'Yes — store toast counts when Untappd exposes them', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Optional engagement metadata.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Fallback image', 'jardin-toasts' ); ?></th>
								<td>
									<?php
									$jt_use_ph = JT_Settings::get( 'jt_use_placeholder_image' );
									?>
									<label class="jt-toggle">
										<input type="hidden" name="jt_use_placeholder_image" value="0" />
										<input name="jt_use_placeholder_image" type="checkbox" value="1" id="jt_use_placeholder_image" <?php checked( $jt_use_ph ); ?> />
										<span><?php esc_html_e( 'Use a fallback image when Untappd download fails (on by default). Uncheck to leave the post without a featured image.', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'Pick a generic beer or logo from your Media Library (no attachment ID to type). There is no built-in remote beer-photo API (keys, licensing, reliability); you can wire Open Food Facts, Wikimedia, or your own source in PHP with the jardin_toasts_placeholder_attachment_id filter (jt_placeholder_attachment_id is still fired for backward compatibility), returning a Media Library attachment ID you created.', 'jardin-toasts' ); ?></p>
									<div class="jt-placeholder-picker" id="jt-placeholder-picker" style="<?php echo $jt_use_ph ? '' : 'display:none;'; ?>">
										<input type="hidden" name="jt_placeholder_image_id" id="jt_placeholder_image_id" value="<?php echo esc_attr( (string) (int) JT_Settings::get( 'jt_placeholder_image_id' ) ); ?>" />
										<p class="jt-placeholder-picker__actions">
											<button type="button" class="button" id="jt-placeholder-select"><?php esc_html_e( 'Choose image', 'jardin-toasts' ); ?></button>
											<button type="button" class="button-link" id="jt-placeholder-clear"><?php esc_html_e( 'Remove', 'jardin-toasts' ); ?></button>
										</p>
										<div class="jt-placeholder-picker__preview" id="jt-placeholder-preview" aria-live="polite"></div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jt-panel" id="jt-ratings-section">
					<div class="jt-panel__header">
						<h2 class="jt-panel__title"><?php esc_html_e( 'Ratings & stars', 'jardin-toasts' ); ?></h2>
						<p class="jt-panel__summary"><?php esc_html_e( 'Map Untappd’s 0–5 raw score into whole stars. Rules are tested in order; the first matching min/max band wins. The jardin_toasts_rating_rules filter (jt_rating_rules is still fired for backward compatibility) can override in code.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jt-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Use mapping rules', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jt-toggle">
										<input type="hidden" name="jt_rating_rounding_enabled" value="0" />
										<input name="jt_rating_rounding_enabled" type="checkbox" value="1" <?php checked( JT_Settings::get( 'jt_rating_rounding_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Map fractional Untappd ratings to whole stars', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
						</table>

						<div class="jt-rating-edit">
							<h3><?php esc_html_e( 'Raw score bands', 'jardin-toasts' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Each row is one band: if the raw rating is between min and max (inclusive), it maps to the chosen star level.', 'jardin-toasts' ); ?></p>
							<table class="widefat striped jt-rating-edit-table">
								<thead>
									<tr>
										<th scope="col" class="jt-rating-edit-table__n"><?php esc_html_e( '#', 'jardin-toasts' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Min (raw)', 'jardin-toasts' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Max (raw)', 'jardin-toasts' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Star level', 'jardin-toasts' ); ?></th>
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
											<td class="jt-rating-edit-table__n"><?php echo esc_html( (string) ( $i + 1 ) ); ?></td>
											<td>
												<label class="screen-reader-text" for="jt-rating-min-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d min', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<input name="jt_rating_rules[<?php echo esc_attr( (string) $i ); ?>][min]" id="jt-rating-min-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmin ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="jt-rating-max-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d max', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<input name="jt_rating_rules[<?php echo esc_attr( (string) $i ); ?>][max]" id="jt-rating-max-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmax ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="jt-rating-round-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d stars', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<select name="jt_rating_rules[<?php echo esc_attr( (string) $i ); ?>][round]" id="jt-rating-round-<?php echo esc_attr( (string) $i ); ?>">
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

							<h3><?php esc_html_e( 'Labels per star level', 'jardin-toasts' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Optional text for each rounded level (for themes, blocks, or future UI).', 'jardin-toasts' ); ?></p>
							<table class="widefat striped jt-rating-edit-table jt-rating-edit-table--labels" role="presentation">
								<tbody>
									<?php
									for ( $s = 0; $s <= 5; $s++ ) {
										$lid = 'jt-rating-label-' . $s;
										?>
										<tr>
											<th scope="row">
												<label for="<?php echo esc_attr( $lid ); ?>"><?php echo esc_html( sprintf( /* translators: %d: star count 0-5 */ __( '%d stars', 'jardin-toasts' ), $s ) ); ?></label>
											</th>
											<td>
												<input name="jt_rating_labels[<?php echo esc_attr( (string) $s ); ?>]" id="<?php echo esc_attr( $lid ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( (string) $labels[ $s ] ); ?>" />
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
