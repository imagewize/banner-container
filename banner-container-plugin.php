<?php
/**
 * Plugin Name: Banner Container Plugin
 * Plugin URI: https://yourwebsite.com/banner-container-plugin
 * Description: Add banner iframes to different locations in your WordPress theme.
 * Version: 1.0.1
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * Text Domain: banner-container-plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('BANNER_CONTAINER_VERSION', '1.0.1');
define('BANNER_CONTAINER_PATH', plugin_dir_path(__FILE__));
define('BANNER_CONTAINER_URL', plugin_dir_url(__FILE__));

// Include required files
require_once BANNER_CONTAINER_PATH . 'includes/class-banner-container-settings.php';
require_once BANNER_CONTAINER_PATH . 'includes/class-banner-container.php';
require_once BANNER_CONTAINER_PATH . 'includes/class-banner-container-welcome.php';

// Enqueue admin styles
function BANNER_CONTAINER_admin_styles() {
    wp_enqueue_style('banner-container-admin', BANNER_CONTAINER_URL . 'admin/css/banner-container-admin.css', array(), BANNER_CONTAINER_VERSION);
}
add_action('admin_enqueue_scripts', 'BANNER_CONTAINER_admin_styles');

// Initialize the plugin
function BANNER_CONTAINER_init() {
    // Initialize main plugin class
    $BANNER_CONTAINER = new BANNER_CONTAINER();
    $BANNER_CONTAINER->init();
    
    // Initialize welcome page (only needed for admin)
    if (is_admin()) {
        new BANNER_CONTAINER_Welcome();
    }
}
add_action('plugins_loaded', 'BANNER_CONTAINER_init');

// Plugin activation hook
function BANNER_CONTAINER_activate() {
    // Set transient for welcome page redirect
    set_transient('BANNER_CONTAINER_activation_redirect', true, 30);
}
register_activation_hook(__FILE__, 'BANNER_CONTAINER_activate');
