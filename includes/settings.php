<?php
/**
 * Settings: site-wide default, fallback taxonomy, and per-term defaults.
 *
 * @package PostCTAs
 */

defined( 'ABSPATH' ) || exit;

/**
 * A <select> of published CTAs.
 *
 * @param string $name     Field name.
 * @param int    $selected Selected CTA ID.
 */
function pcta_cta_dropdown( $name, $selected ) {
	$ctas = get_posts(
		array(
			'post_type'   => 'pcta_cta',
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		)
	);

	printf( '<select name="%s" id="%s">', esc_attr( $name ), esc_attr( $name ) );
	printf( '<option value="0">%s</option>', esc_html__( '— None —', 'post-ctas' ) );
	foreach ( $ctas as $cta ) {
		printf(
			'<option value="%d" %s>%s</option>',
			(int) $cta->ID,
			selected( (int) $selected, $cta->ID, false ),
			esc_html( get_the_title( $cta ) )
		);
	}
	echo '</select>';
}

/**
 * Register the two site-wide settings.
 */
function pcta_register_settings() {
	register_setting( 'pcta_settings', 'pcta_default_cta', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0 ) );
	register_setting(
		'pcta_settings',
		'pcta_taxonomy',
		array(
			'type'              => 'string',
			'default'           => 'category',
			'sanitize_callback' => fn( $value ) => taxonomy_exists( $value ) ? $value : 'category',
		)
	);
}
add_action( 'admin_init', 'pcta_register_settings' );

/**
 * Add Settings under the CTAs menu.
 */
function pcta_add_settings_page() {
	add_submenu_page(
		'edit.php?post_type=pcta_cta',
		__( 'CTA Settings', 'post-ctas' ),
		__( 'Settings', 'post-ctas' ),
		'manage_options',
		'pcta-settings',
		'pcta_render_settings_page'
	);
}
add_action( 'admin_menu', 'pcta_add_settings_page' );

/**
 * Output the settings page.
 */
function pcta_render_settings_page() {
	$taxonomies = get_object_taxonomies( 'post', 'objects' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'CTA Settings', 'post-ctas' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'pcta_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="pcta_default_cta"><?php esc_html_e( 'Site-wide default CTA', 'post-ctas' ); ?></label></th>
					<td>
						<?php pcta_cta_dropdown( 'pcta_default_cta', get_option( 'pcta_default_cta', 0 ) ); ?>
						<p class="description"><?php esc_html_e( 'Shown on posts with no CTA block and no term default.', 'post-ctas' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="pcta_taxonomy"><?php esc_html_e( 'Fallback taxonomy', 'post-ctas' ); ?></label></th>
					<td>
						<select name="pcta_taxonomy" id="pcta_taxonomy">
							<?php foreach ( $taxonomies as $tax ) : ?>
								<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( get_option( 'pcta_taxonomy', 'category' ), $tax->name ); ?>><?php echo esc_html( $tax->labels->singular_name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Set a default CTA for each term on its edit screen.', 'post-ctas' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Show a "Default CTA" field on the fallback taxonomy's term edit screen.
 *
 * @param WP_Term $term Term being edited.
 */
function pcta_term_field( $term ) {
	wp_nonce_field( 'pcta_term_save', 'pcta_term_nonce' );
	?>
	<tr class="form-field">
		<th scope="row"><label for="pcta_default_cta"><?php esc_html_e( 'Default CTA', 'post-ctas' ); ?></label></th>
		<td>
			<?php pcta_cta_dropdown( 'pcta_default_cta', get_term_meta( $term->term_id, 'pcta_default_cta', true ) ); ?>
			<p class="description"><?php esc_html_e( 'Shown on posts in this term that have no CTA block.', 'post-ctas' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Save the term field.
 *
 * @param int $term_id Term ID.
 */
function pcta_save_term_field( $term_id ) {
	if ( ! isset( $_POST['pcta_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['pcta_term_nonce'] ), 'pcta_term_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}
	update_term_meta( $term_id, 'pcta_default_cta', absint( $_POST['pcta_default_cta'] ?? 0 ) );
}

/**
 * Attach the term field to whichever taxonomy is chosen in settings.
 */
function pcta_hook_term_fields() {
	$taxonomy = get_option( 'pcta_taxonomy', 'category' );
	add_action( "{$taxonomy}_edit_form_fields", 'pcta_term_field' );
	add_action( "edited_{$taxonomy}", 'pcta_save_term_field' );
}
add_action( 'admin_init', 'pcta_hook_term_fields' );
