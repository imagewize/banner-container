<?php
/**
 * Plugin Name: Banner Container Plugin
 * Plugin URI: https://imagewize.com/iwz-banner-container-plugin
 * Description: Add banners to different locations in your WordPress theme.
 * Version: 1.5.3
 * Author: Jasper Frumau
 * Author URI: https://imagewize.com
 * License: GPL-2.0+
 * Text Domain: banner-container-plugin
 *
 * @package IWZ_Banner_Container
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'IWZ_BANNER_CONTAINER_VERSION', '1.5.3' );
define( 'IWZ_BANNER_CONTAINER_PATH', plugin_dir_path( __FILE__ ) );
define( 'IWZ_BANNER_CONTAINER_URL', plugin_dir_url( __FILE__ ) );

// Include required files.
require_once IWZ_BANNER_CONTAINER_PATH . 'includes/class-iwz-banner-container-settings.php';
require_once IWZ_BANNER_CONTAINER_PATH . 'includes/class-iwz-banner-container.php';
require_once IWZ_BANNER_CONTAINER_PATH . 'includes/class-iwz-banner-container-welcome.php';

/**
 * Enqueue admin styles.
 */
function iwz_banner_container_admin_styles() {
	wp_enqueue_style( 'iwz-banner-container-admin', IWZ_BANNER_CONTAINER_URL . 'admin/css/iwz-banner-container-admin.css', array(), IWZ_BANNER_CONTAINER_VERSION );
}
add_action( 'admin_enqueue_scripts', 'iwz_banner_container_admin_styles' );

/**
 * Load plugin textdomain for translations.
 */
function iwz_banner_container_load_textdomain() {
	load_plugin_textdomain( 'banner-container-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'iwz_banner_container_load_textdomain' );

/**
 * Initialize the plugin.
 */
function iwz_banner_container_init() {
	// Initialize main plugin class.
	$banner_container = new IWZ_Banner_Container();
	$banner_container->init();

	// Initialize welcome page (only needed for admin).
	if ( is_admin() ) {
		new IWZ_Banner_Container_Welcome();
	}
}
add_action( 'plugins_loaded', 'iwz_banner_container_init' );

/**
 * Plugin activation hook.
 */
function iwz_banner_container_activate() {
	// Set transient for welcome page redirect.
	set_transient( 'iwz_banner_container_activation_redirect', true, 30 );
}
register_activation_hook( __FILE__, 'iwz_banner_container_activate' );
