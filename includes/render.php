<?php
/**
 * Front-end markup for a CTA.
 *
 * @package CampaignCTAs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the HTML for one CTA.
 *
 * Content is read live from the CTA post, so editing a CTA updates every
 * post that shows it.
 *
 * @param int $cta_id CTA post ID.
 * @return string Empty string if the CTA is missing or unpublished.
 */
function ccta_render_cta( $cta_id ) {
	$cta = get_post( $cta_id );
	if ( ! $cta || 'ccta_cta' !== $cta->post_type || 'publish' !== $cta->post_status ) {
		return '';
	}

	wp_enqueue_style( 'ccta', CCTA_URL . 'style.css', array(), CCTA_VERSION );

	$heading     = get_post_meta( $cta->ID, 'ccta_heading', true );
	$body        = get_post_meta( $cta->ID, 'ccta_body', true );
	$button_text = get_post_meta( $cta->ID, 'ccta_button_text', true );
	$button_url  = get_post_meta( $cta->ID, 'ccta_button_url', true );
	$theme       = get_post_meta( $cta->ID, 'ccta_theme', true ) ?: 'dark';

	ob_start();
	?>
	<aside class="ccta ccta--<?php echo esc_attr( $theme ); ?>">
		<?php if ( has_post_thumbnail( $cta ) ) : ?>
			<div class="ccta__image"><?php echo get_the_post_thumbnail( $cta, 'medium' ); ?></div>
		<?php endif; ?>
		<div class="ccta__content">
			<?php if ( $heading ) : ?>
				<p class="ccta__heading"><?php echo esc_html( $heading ); ?></p>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<p class="ccta__body"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
			<?php if ( $button_text && $button_url ) : ?>
				<a class="ccta__button" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $button_text ); ?></a>
			<?php endif; ?>
		</div>
	</aside>
	<?php
	return ob_get_clean();
}
