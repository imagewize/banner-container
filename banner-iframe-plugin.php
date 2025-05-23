<?php
/**
 * Plugin Name: Banner Iframe Plugin
 * Plugin URI: https://yourwebsite.com/banner-iframe-plugin
 * Description: Add banner iframes to different locations in your WordPress theme.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * Text Domain: banner-iframe-plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('BANNER_IFRAME_VERSION', '1.0.0');
define('BANNER_IFRAME_PATH', plugin_dir_path(__FILE__));
define('BANNER_IFRAME_URL', plugin_dir_url(__FILE__));

// Include required files
require_once BANNER_IFRAME_PATH . 'includes/class-banner-iframe-settings.php';
require_once BANNER_IFRAME_PATH . 'includes/class-banner-iframe.php';
require_once BANNER_IFRAME_PATH . 'includes/class-banner-iframe-welcome.php';

// Enqueue admin styles
function banner_iframe_admin_styles() {
    wp_enqueue_style('banner-iframe-admin', BANNER_IFRAME_URL . 'admin/css/banner-iframe-admin.css', array(), BANNER_IFRAME_VERSION);
}
add_action('admin_enqueue_scripts', 'banner_iframe_admin_styles');

// Initialize the plugin
function banner_iframe_init() {
    $banner_iframe = new Banner_Iframe();
    $banner_iframe->init();
    
    // Initialize welcome page
    new Banner_Iframe_Welcome();
}
add_action('plugins_loaded', 'banner_iframe_init');

// Plugin activation hook
function banner_iframe_activate() {
    // Set transient for welcome page redirect
    set_transient('banner_iframe_activation_redirect', true, 30);
}
register_activation_hook(__FILE__, 'banner_iframe_activate');
