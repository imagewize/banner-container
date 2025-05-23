<?php
/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Instead of direct database call, use WordPress functions.

// Security check.

// Define plugin options to delete.
$all_options = array(
	// List all plugin options here.
	'banner_wp_head_enabled',
	'banner_wp_head_code',
	'banner_wp_footer_enabled',
	'banner_wp_footer_code',
	'banner_the_content_enabled',
	'banner_the_content_code',
	'banner_content_position',
	'banner_content_paragraph',
	'banner_content_post_types',
	'banner_get_sidebar_enabled',
	'banner_get_sidebar_code',
	'banner_wp_nav_menu_items_enabled',
	'banner_wp_nav_menu_items_code',
	'banner_iframe_welcome_dismissed',
);

// Delete each option individually.
foreach ( $all_options as $option ) {
	delete_option( $option );
}

// Clear any caches.
wp_cache_flush();
