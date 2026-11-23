<?php
/**
 * Automatic fallback CTAs for posts without one.
 *
 * Order: a CTA block in the post > the default for the post's first term
 * in the fallback taxonomy > the site-wide default.
 *
 * @package CampaignCTAs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Find the fallback CTA for a post.
 *
 * @param int $post_id Post ID.
 * @return int CTA ID, or 0 for none.
 */
function ccta_get_fallback_cta( $post_id ) {
	$taxonomy = get_option( 'ccta_taxonomy', 'category' );
	$terms    = get_the_terms( $post_id, $taxonomy );

	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$cta_id = (int) get_term_meta( $term->term_id, 'ccta_default_cta', true );
			if ( $cta_id ) {
				return $cta_id;
			}
		}
	}

	return (int) get_option( 'ccta_default_cta', 0 );
}

/**
 * Append the fallback CTA to single posts that don't already have one.
 *
 * @param string $content Post content.
 * @return string
 */
function ccta_append_fallback( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( has_block( 'campaign-ctas/cta', get_the_ID() ) ) {
		return $content;
	}

	$cta_id = ccta_get_fallback_cta( get_the_ID() );

	return $cta_id ? $content . ccta_render_cta( $cta_id ) : $content;
}
add_filter( 'the_content', 'ccta_append_fallback' );
