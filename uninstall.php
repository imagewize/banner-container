<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Define banner-related options to delete
$banner_options = array(
	// Header location
	'banner_wp_head_enabled',
	'banner_wp_head_code',

	// Footer location
	'banner_wp_footer_enabled',
	'banner_wp_footer_code',

	// Content location
	'banner_the_content_enabled',
	'banner_the_content_code',
	'banner_content_position',
	'banner_content_paragraph',
	'banner_content_post_types',

	// Sidebar location
	'banner_get_sidebar_enabled',
	'banner_get_sidebar_code',

	// Menu location
	'banner_wp_nav_menu_items_enabled',
	'banner_wp_nav_menu_items_code',
);

// Delete each option
foreach ( $banner_options as $option ) {
	delete_option( $option );
}

// Also find and delete any custom locations that might have been added
global $wpdb;
$custom_options = $wpdb->get_results(
	"SELECT option_name FROM {$wpdb->options} 
     WHERE option_name LIKE 'banner_%_enabled' 
     OR option_name LIKE 'banner_%_code'"
);

foreach ( $custom_options as $option ) {
	delete_option( $option->option_name );
}
