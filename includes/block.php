<?php
/**
 * The "CTA" block writers add to posts.
 *
 * @package PostCTAs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the block from block.json.
 *
 * It's a dynamic block: the post only stores the CTA's ID, and the markup is
 * rendered on each page view, so CTA edits show up everywhere.
 */
function pcta_register_block() {
	register_block_type(
		PCTA_DIR . 'block',
		array(
			'render_callback' => fn( $attributes ) => pcta_render_cta( (int) ( $attributes['ctaId'] ?? 0 ) ),
		)
	);
}
add_action( 'init', 'pcta_register_block' );
