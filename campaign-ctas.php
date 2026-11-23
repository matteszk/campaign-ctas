<?php
/**
 * Plugin Name:       Campaign CTAs
 * Plugin URI:        https://github.com/matteszk/campaign-ctas
 * Description:       Reusable call-to-action banners for posts. Edit a CTA once and it updates everywhere, with automatic fallbacks by category.
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            Matthew Szklarz
 * Author URI:        https://matthewszklarz.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       campaign-ctas
 */

// Block direct access to this file outside WordPress.
defined( 'ABSPATH' ) || exit;

define( 'CCTA_VERSION', '0.1.0' );
define( 'CCTA_DIR', plugin_dir_path( __FILE__ ) );
define( 'CCTA_URL', plugin_dir_url( __FILE__ ) );

require CCTA_DIR . 'includes/post-type.php';
require CCTA_DIR . 'includes/render.php';
require CCTA_DIR . 'includes/block.php';
require CCTA_DIR . 'includes/fallback.php';
require CCTA_DIR . 'includes/settings.php';

// Register the post type before flushing so its URLs are known, then clean up on deactivate.
register_activation_hook(
	__FILE__,
	function () {
		ccta_register_post_type();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
