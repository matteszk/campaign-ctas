<?php
/**
 * Plugin Name:       Post CTAs
 * Plugin URI:        https://github.com/matteszk/post-ctas
 * Description:       Reusable call-to-action banners for posts. Edit a CTA once and it updates everywhere, with automatic fallbacks by category.
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            Matthew Szklarz
 * Author URI:        https://matthewszklarz.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       post-ctas
 */

// Block direct access to this file outside WordPress.
defined( 'ABSPATH' ) || exit;

define( 'PCTA_VERSION', '0.1.0' );
define( 'PCTA_DIR', plugin_dir_path( __FILE__ ) );
define( 'PCTA_URL', plugin_dir_url( __FILE__ ) );

require PCTA_DIR . 'includes/post-type.php';
require PCTA_DIR . 'includes/render.php';
require PCTA_DIR . 'includes/block.php';
require PCTA_DIR . 'includes/fallback.php';
require PCTA_DIR . 'includes/settings.php';

// Register the post type before flushing so its URLs are known, then clean up on deactivate.
register_activation_hook(
	__FILE__,
	function () {
		pcta_register_post_type();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
