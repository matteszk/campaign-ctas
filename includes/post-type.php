<?php
/**
 * The CTA post type and its fields.
 *
 * @package CampaignCTAs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fields stored on each CTA, as meta key => sanitize callback.
 *
 * The title is for editors only; the heading is what visitors see.
 * The featured image is used as the CTA image.
 */
function ccta_fields() {
	return array(
		'ccta_heading'     => 'sanitize_text_field',
		'ccta_body'        => 'sanitize_textarea_field',
		'ccta_button_text' => 'sanitize_text_field',
		'ccta_button_url'  => 'esc_url_raw',
		'ccta_theme'       => 'ccta_sanitize_theme',
	);
}

/**
 * Color themes a CTA can use. Each maps to a CSS class.
 */
function ccta_themes() {
	return array(
		'dark'  => __( 'Dark', 'campaign-ctas' ),
		'light' => __( 'Light', 'campaign-ctas' ),
		'brand' => __( 'Brand', 'campaign-ctas' ),
	);
}

/**
 * Only allow known themes.
 *
 * @param string $value Submitted theme.
 * @return string
 */
function ccta_sanitize_theme( $value ) {
	return array_key_exists( $value, ccta_themes() ) ? $value : 'dark';
}

/**
 * Register the post type and its meta.
 */
function ccta_register_post_type() {
	register_post_type(
		'ccta_cta',
		array(
			'labels'       => array(
				'name'          => __( 'CTAs', 'campaign-ctas' ),
				'singular_name' => __( 'CTA', 'campaign-ctas' ),
				'add_new_item'  => __( 'Add New CTA', 'campaign-ctas' ),
				'edit_item'     => __( 'Edit CTA', 'campaign-ctas' ),
			),
			'public'       => false,
			'show_ui'      => true,
			// Needed so the block editor can list CTAs over the REST API.
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-megaphone',
			'supports'     => array( 'title', 'thumbnail' ),
		)
	);

	foreach ( ccta_fields() as $key => $sanitize ) {
		register_post_meta(
			'ccta_cta',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => $sanitize,
				'show_in_rest'      => true,
				'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
			)
		);
	}
}
add_action( 'init', 'ccta_register_post_type' );

/**
 * Add the fields box to the CTA edit screen.
 */
function ccta_add_meta_box() {
	add_meta_box( 'ccta_fields', __( 'CTA content', 'campaign-ctas' ), 'ccta_render_meta_box', 'ccta_cta', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'ccta_add_meta_box' );

/**
 * Output the fields box.
 *
 * @param WP_Post $post The CTA being edited.
 */
function ccta_render_meta_box( $post ) {
	wp_nonce_field( 'ccta_save', 'ccta_nonce' );
	$get = fn( $key ) => get_post_meta( $post->ID, $key, true );
	?>
	<p>
		<label for="ccta_heading"><strong><?php esc_html_e( 'Heading', 'campaign-ctas' ); ?></strong></label><br>
		<input type="text" id="ccta_heading" name="ccta_heading" class="widefat" value="<?php echo esc_attr( $get( 'ccta_heading' ) ); ?>">
	</p>
	<p>
		<label for="ccta_body"><strong><?php esc_html_e( 'Body', 'campaign-ctas' ); ?></strong></label><br>
		<textarea id="ccta_body" name="ccta_body" class="widefat" rows="3"><?php echo esc_textarea( $get( 'ccta_body' ) ); ?></textarea>
	</p>
	<p>
		<label for="ccta_button_text"><strong><?php esc_html_e( 'Button text', 'campaign-ctas' ); ?></strong></label><br>
		<input type="text" id="ccta_button_text" name="ccta_button_text" class="widefat" value="<?php echo esc_attr( $get( 'ccta_button_text' ) ); ?>">
	</p>
	<p>
		<label for="ccta_button_url"><strong><?php esc_html_e( 'Button link', 'campaign-ctas' ); ?></strong></label><br>
		<input type="url" id="ccta_button_url" name="ccta_button_url" class="widefat" value="<?php echo esc_attr( $get( 'ccta_button_url' ) ); ?>">
	</p>
	<p>
		<label for="ccta_theme"><strong><?php esc_html_e( 'Theme', 'campaign-ctas' ); ?></strong></label><br>
		<select id="ccta_theme" name="ccta_theme">
			<?php foreach ( ccta_themes() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $get( 'ccta_theme' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Use the featured image to add an image to this CTA.', 'campaign-ctas' ); ?></p>
	<?php
}

/**
 * Save the fields box.
 *
 * @param int $post_id The CTA being saved.
 */
function ccta_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['ccta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ccta_nonce'] ), 'ccta_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( ccta_fields() as $key => $sanitize ) {
		if ( isset( $_POST[ $key ] ) ) {
			// register_post_meta's sanitize_callback runs inside update_post_meta.
			update_post_meta( $post_id, $key, wp_unslash( $_POST[ $key ] ) );
		}
	}
}
add_action( 'save_post_ccta_cta', 'ccta_save_meta_box' );
