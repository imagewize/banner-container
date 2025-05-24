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
	'iwz_banner_wp_head_enabled',
	'iwz_banner_wp_head_code',
	'iwz_banner_wp_head_banners',

	// Footer location
	'iwz_banner_wp_footer_enabled',
	'iwz_banner_wp_footer_code',
	'iwz_banner_wp_footer_banners',

	// Content location
	'iwz_banner_the_content_enabled',
	'iwz_banner_the_content_code',
	'iwz_banner_the_content_banners',
	'iwz_banner_content_position',
	'iwz_banner_content_paragraph',
	'iwz_banner_content_post_types',
	'iwz_banner_content_banners', // Legacy multiple banners option

	// Sidebar location
	'iwz_banner_get_sidebar_enabled',
	'iwz_banner_get_sidebar_code',
	'iwz_banner_get_sidebar_banners',

	// Menu location
	'iwz_banner_wp_nav_menu_items_enabled',
	'iwz_banner_wp_nav_menu_items_code',
	'iwz_banner_wp_nav_menu_items_banners',
);

// Delete each option
foreach ( $banner_options as $option ) {
	delete_option( $option );
}

// Also find and delete any custom locations that might have been added
global $wpdb;
$custom_options = $wpdb->get_results(
	"SELECT option_name FROM {$wpdb->options} 
     WHERE option_name LIKE 'iwz_banner_%_enabled' 
     OR option_name LIKE 'iwz_banner_%_code'"
);

foreach ( $custom_options as $option ) {
	delete_option( $option->option_name );
}
