<?php
/**
 * The CTA post type and its fields.
 *
 * @package PostCTAs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fields stored on each CTA, as meta key => sanitize callback.
 *
 * The title is for editors only; the heading is what visitors see.
 * The featured image is used as the CTA image.
 */
function pcta_fields() {
	return array(
		'pcta_heading'     => 'sanitize_text_field',
		'pcta_body'        => 'sanitize_textarea_field',
		'pcta_button_text' => 'sanitize_text_field',
		'pcta_button_url'  => 'esc_url_raw',
		'pcta_theme'       => 'pcta_sanitize_theme',
	);
}

/**
 * Color themes a CTA can use. Each maps to a CSS class.
 */
function pcta_themes() {
	return array(
		'dark'  => __( 'Dark', 'post-ctas' ),
		'light' => __( 'Light', 'post-ctas' ),
		'brand' => __( 'Brand', 'post-ctas' ),
	);
}

/**
 * Only allow known themes.
 *
 * @param string $value Submitted theme.
 * @return string
 */
function pcta_sanitize_theme( $value ) {
	return array_key_exists( $value, pcta_themes() ) ? $value : 'dark';
}

/**
 * Register the post type and its meta.
 */
function pcta_register_post_type() {
	register_post_type(
		'pcta_cta',
		array(
			'labels'       => array(
				'name'          => __( 'CTAs', 'post-ctas' ),
				'singular_name' => __( 'CTA', 'post-ctas' ),
				'add_new_item'  => __( 'Add New CTA', 'post-ctas' ),
				'edit_item'     => __( 'Edit CTA', 'post-ctas' ),
			),
			'public'       => false,
			'show_ui'      => true,
			// Needed so the block editor can list CTAs over the REST API.
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-megaphone',
			'supports'     => array( 'title', 'thumbnail' ),
		)
	);

	foreach ( pcta_fields() as $key => $sanitize ) {
		register_post_meta(
			'pcta_cta',
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
add_action( 'init', 'pcta_register_post_type' );

/**
 * Add the fields box to the CTA edit screen.
 */
function pcta_add_meta_box() {
	add_meta_box( 'pcta_fields', __( 'CTA content', 'post-ctas' ), 'pcta_render_meta_box', 'pcta_cta', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'pcta_add_meta_box' );

/**
 * Output the fields box.
 *
 * @param WP_Post $post The CTA being edited.
 */
function pcta_render_meta_box( $post ) {
	wp_nonce_field( 'pcta_save', 'pcta_nonce' );
	$get = fn( $key ) => get_post_meta( $post->ID, $key, true );
	?>
	<p>
		<label for="pcta_heading"><strong><?php esc_html_e( 'Heading', 'post-ctas' ); ?></strong></label><br>
		<input type="text" id="pcta_heading" name="pcta_heading" class="widefat" value="<?php echo esc_attr( $get( 'pcta_heading' ) ); ?>">
	</p>
	<p>
		<label for="pcta_body"><strong><?php esc_html_e( 'Body', 'post-ctas' ); ?></strong></label><br>
		<textarea id="pcta_body" name="pcta_body" class="widefat" rows="3"><?php echo esc_textarea( $get( 'pcta_body' ) ); ?></textarea>
	</p>
	<p>
		<label for="pcta_button_text"><strong><?php esc_html_e( 'Button text', 'post-ctas' ); ?></strong></label><br>
		<input type="text" id="pcta_button_text" name="pcta_button_text" class="widefat" value="<?php echo esc_attr( $get( 'pcta_button_text' ) ); ?>">
	</p>
	<p>
		<label for="pcta_button_url"><strong><?php esc_html_e( 'Button link', 'post-ctas' ); ?></strong></label><br>
		<input type="url" id="pcta_button_url" name="pcta_button_url" class="widefat" value="<?php echo esc_attr( $get( 'pcta_button_url' ) ); ?>">
	</p>
	<p>
		<label for="pcta_theme"><strong><?php esc_html_e( 'Theme', 'post-ctas' ); ?></strong></label><br>
		<select id="pcta_theme" name="pcta_theme">
			<?php foreach ( pcta_themes() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $get( 'pcta_theme' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Use the featured image to add an image to this CTA.', 'post-ctas' ); ?></p>
	<?php
}

/**
 * Save the fields box.
 *
 * @param int $post_id The CTA being saved.
 */
function pcta_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['pcta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['pcta_nonce'] ), 'pcta_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( pcta_fields() as $key => $sanitize ) {
		if ( isset( $_POST[ $key ] ) ) {
			// register_post_meta's sanitize_callback runs inside update_post_meta.
			update_post_meta( $post_id, $key, wp_unslash( $_POST[ $key ] ) );
		}
	}
}
add_action( 'save_post_pcta_cta', 'pcta_save_meta_box' );
