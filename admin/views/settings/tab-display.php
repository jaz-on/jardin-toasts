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

$rule_template = jardin_toasts_get_default_rating_rules();
$rules         = Jardin_Toasts_Settings::get( 'jardin_toasts_rating_rules' );
if ( ! is_array( $rules ) ) {
	$rules = $rule_template;
}
$rules = array_values( $rules );
while ( count( $rules ) < count( $rule_template ) ) {
	$rules[] = $rule_template[ count( $rules ) ];
}
$rules = array_slice( $rules, 0, count( $rule_template ) );

$default_labels = jardin_toasts_get_default_rating_labels();
$labels         = Jardin_Toasts_Settings::get( 'jardin_toasts_rating_labels' );
if ( ! is_array( $labels ) ) {
	$labels = $default_labels;
}
for ( $li = 0; $li <= 5; $li++ ) {
	if ( ! isset( $labels[ $li ] ) ) {
		$labels[ $li ] = isset( $default_labels[ $li ] ) ? $default_labels[ $li ] : '';
	}
}
?>
			<div class="jardin-toasts-tab-panel-body"<?php echo 'display' !== $tab ? ' hidden' : ''; ?> data-jardin-toasts-tab="display">

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Archives & layout', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Choose how lists of check-ins appear on the main journal and taxonomy pages.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="jardin_toasts_archive_layout"><?php esc_html_e( 'Archive layout', 'jardin-toasts' ); ?></label></th>
								<td>
									<select name="jardin_toasts_archive_layout" id="jardin_toasts_archive_layout">
										<option value="grid" <?php selected( Jardin_Toasts_Settings::get( 'jardin_toasts_archive_layout' ), 'grid' ); ?>><?php esc_html_e( 'Grid — cards with photos', 'jardin-toasts' ); ?></option>
										<option value="table" <?php selected( Jardin_Toasts_Settings::get( 'jardin_toasts_archive_layout' ), 'table' ); ?>><?php esc_html_e( 'Table — compact rows', 'jardin-toasts' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Applies to the beer check-in archive and beer style / brewery / venue listings.', 'jardin-toasts' ); ?></p>
									<?php
									$archive_url = jardin_toasts_get_checkin_archive_url();
									?>
									<p class="description jardin-toasts-archive-url">
										<?php echo esc_html__( 'Public journal URL:', 'jardin-toasts' ); ?>
										<a href="<?php echo esc_url( $archive_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $archive_url ); ?></a>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jardin-toasts-panel">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Imported content', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Control media and taxonomy data saved with each check-in. Check-in notes are stored as post content with automatic paragraphs for plain text.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Beer photos', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_import_images" value="0" />
										<input name="jardin_toasts_import_images" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_import_images' ) ); ?> />
										<span><?php esc_html_e( 'Yes — import beer photos into the Media Library', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default for new installs. Turn off if you only want text.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Venues', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_import_venues" value="0" />
										<input name="jardin_toasts_import_venues" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_import_venues' ) ); ?> />
										<span><?php esc_html_e( 'Yes — create venue taxonomy terms when a location is present', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Lets you browse check-ins by place.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Social stats', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_import_social_data" value="0" />
										<input name="jardin_toasts_import_social_data" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_import_social_data' ) ); ?> />
										<span><?php esc_html_e( 'Yes — store toast counts when Untappd exposes them', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'On by default. Optional engagement metadata.', 'jardin-toasts' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Fallback image', 'jardin-toasts' ); ?></th>
								<td>
									<?php
									$jardin_toasts_use_ph = Jardin_Toasts_Settings::get( 'jardin_toasts_use_placeholder_image' );
									?>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_use_placeholder_image" value="0" />
										<input name="jardin_toasts_use_placeholder_image" type="checkbox" value="1" id="jardin_toasts_use_placeholder_image" <?php checked( $jardin_toasts_use_ph ); ?> />
										<span><?php esc_html_e( 'Use a fallback image when Untappd download fails (on by default). Uncheck to leave the post without a featured image.', 'jardin-toasts' ); ?></span>
									</label>
									<p class="description"><?php esc_html_e( 'Pick a generic beer or logo from your Media Library (no attachment ID to type). There is no built-in remote beer-photo API (keys, licensing, reliability); you can wire Open Food Facts, Wikimedia, or your own source in PHP with the jardin_toasts_placeholder_attachment_id filter (jardin_toasts_placeholder_attachment_id is still fired for backward compatibility), returning a Media Library attachment ID you created.', 'jardin-toasts' ); ?></p>
									<div class="jardin-toasts-placeholder-picker" id="jardin-toasts-placeholder-picker" style="<?php echo $jardin_toasts_use_ph ? '' : 'display:none;'; ?>">
										<input type="hidden" name="jardin_toasts_placeholder_image_id" id="jardin_toasts_placeholder_image_id" value="<?php echo esc_attr( (string) (int) Jardin_Toasts_Settings::get( 'jardin_toasts_placeholder_image_id' ) ); ?>" />
										<p class="jardin-toasts-placeholder-picker__actions">
											<button type="button" class="button" id="jardin-toasts-placeholder-select"><?php esc_html_e( 'Choose image', 'jardin-toasts' ); ?></button>
											<button type="button" class="button-link" id="jardin-toasts-placeholder-clear"><?php esc_html_e( 'Remove', 'jardin-toasts' ); ?></button>
										</p>
										<div class="jardin-toasts-placeholder-picker__preview" id="jardin-toasts-placeholder-preview" aria-live="polite"></div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="jardin-toasts-panel" id="jardin-toasts-ratings-section">
					<div class="jardin-toasts-panel__header">
						<h2 class="jardin-toasts-panel__title"><?php esc_html_e( 'Ratings & stars', 'jardin-toasts' ); ?></h2>
						<p class="jardin-toasts-panel__summary"><?php esc_html_e( 'Map Untappd’s 0–5 raw score into whole stars. Rules are tested in order; the first matching min/max band wins. The jardin_toasts_rating_rules filter (jardin_toasts_rating_rules is still fired for backward compatibility) can override in code.', 'jardin-toasts' ); ?></p>
					</div>
					<div class="jardin-toasts-panel__body">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Use mapping rules', 'jardin-toasts' ); ?></th>
								<td>
									<label class="jardin-toasts-toggle">
										<input type="hidden" name="jardin_toasts_rating_rounding_enabled" value="0" />
										<input name="jardin_toasts_rating_rounding_enabled" type="checkbox" value="1" <?php checked( Jardin_Toasts_Settings::get( 'jardin_toasts_rating_rounding_enabled' ) ); ?> />
										<span><?php esc_html_e( 'Map fractional Untappd ratings to whole stars', 'jardin-toasts' ); ?></span>
									</label>
								</td>
							</tr>
						</table>

						<div class="jardin-toasts-rating-edit">
							<h3><?php esc_html_e( 'Raw score bands', 'jardin-toasts' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Each row is one band: if the raw rating is between min and max (inclusive), it maps to the chosen star level.', 'jardin-toasts' ); ?></p>
							<table class="widefat striped jardin-toasts-rating-edit-table">
								<thead>
									<tr>
										<th scope="col" class="jardin-toasts-rating-edit-table__n"><?php esc_html_e( '#', 'jardin-toasts' ); ?></th>
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
											<td class="jardin-toasts-rating-edit-table__n"><?php echo esc_html( (string) ( $i + 1 ) ); ?></td>
											<td>
												<label class="screen-reader-text" for="jardin-toasts-rating-min-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d min', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<input name="jardin_toasts_rating_rules[<?php echo esc_attr( (string) $i ); ?>][min]" id="jardin-toasts-rating-min-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmin ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="jardin-toasts-rating-max-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d max', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<input name="jardin_toasts_rating_rules[<?php echo esc_attr( (string) $i ); ?>][max]" id="jardin-toasts-rating-max-<?php echo esc_attr( (string) $i ); ?>" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( (string) $rmax ); ?>" />
											</td>
											<td>
												<label class="screen-reader-text" for="jardin-toasts-rating-round-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf( /* translators: %d: row number */ __( 'Band %d stars', 'jardin-toasts' ), $i + 1 ) ); ?></label>
												<select name="jardin_toasts_rating_rules[<?php echo esc_attr( (string) $i ); ?>][round]" id="jardin-toasts-rating-round-<?php echo esc_attr( (string) $i ); ?>">
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
							<table class="widefat striped jardin-toasts-rating-edit-table jardin-toasts-rating-edit-table--labels" role="presentation">
								<tbody>
									<?php
									for ( $s = 0; $s <= 5; $s++ ) {
										$lid = 'jardin-toasts-rating-label-' . $s;
										?>
										<tr>
											<th scope="row">
												<label for="<?php echo esc_attr( $lid ); ?>"><?php echo esc_html( sprintf( /* translators: %d: star count 0-5 */ __( '%d stars', 'jardin-toasts' ), $s ) ); ?></label>
											</th>
											<td>
												<input name="jardin_toasts_rating_labels[<?php echo esc_attr( (string) $s ); ?>]" id="<?php echo esc_attr( $lid ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( (string) $labels[ $s ] ); ?>" />
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
