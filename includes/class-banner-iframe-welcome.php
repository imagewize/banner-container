<?php
/**
 * Banner Iframe Plugin - Welcome Page
 *
 * @since      1.0.0
 */

class Banner_Iframe_Welcome {

    /**
     * Initialize the welcome page.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_welcome_page'));
        add_action('admin_head', array($this, 'hide_welcome_page_from_menu'));
        add_action('admin_init', array($this, 'redirect_to_welcome_page'));
    }

    /**
     * Add the welcome page to admin menu
     */
    public function add_welcome_page() {
        add_submenu_page(
            'banner-iframe-settings',
            __('Welcome to Banner Iframe Plugin', 'banner-iframe-plugin'),
            __('Welcome', 'banner-iframe-plugin'),
            'manage_options',
            'banner-iframe-welcome',
            array($this, 'display_welcome_page')
        );
    }

    /**
     * Hide the welcome page from the admin menu
     */
    public function hide_welcome_page_from_menu() {
        remove_submenu_page('banner-iframe-settings', 'banner-iframe-welcome');
    }

    /**
     * Redirect to welcome page upon plugin activation
     */
    public function redirect_to_welcome_page() {
        if (get_transient('banner_iframe_activation_redirect')) {
            delete_transient('banner_iframe_activation_redirect');
            if (!isset($_GET['activate-multi'])) {
                wp_redirect(admin_url('admin.php?page=banner-iframe-welcome'));
                exit;
            }
        }
    }

    /**
     * Display welcome page content
     */
    public function display_welcome_page() {
        ?>
        <div class="wrap about-wrap banner-iframe-welcome-wrap">
            <h1><?php _e('Welcome to Banner Iframe Plugin', 'banner-iframe-plugin'); ?></h1>
            
            <div class="about-text">
                <?php _e('Thank you for installing Banner Iframe Plugin! This plugin allows you to easily add banner iframes to different locations in your WordPress theme.', 'banner-iframe-plugin'); ?>
            </div>
            
            <div class="banner-iframe-notice">
                <h4><?php _e('Plugin Information', 'banner-iframe-plugin'); ?></h4>
                <p>
                    <span class="dashicons dashicons-yes" style="color:green;"></span> 
                    <?php _e('Banner Iframe Plugin is ready to use!', 'banner-iframe-plugin'); ?>
                </p>
            </div>
            
            <div class="banner-iframe-section">
                <h2><?php _e('Getting Started', 'banner-iframe-plugin'); ?></h2>
                <p><?php _e('To configure your banner iframes:', 'banner-iframe-plugin'); ?></p>
                <ol>
                    <li><?php _e('Go to the Banner Iframes settings page in your WordPress admin', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('Enable the locations where you want to display banners', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('Enter your iframe or banner HTML code for each location', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('For content banners, choose placement options (top, bottom, or after a specific paragraph)', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('Save your settings', 'banner-iframe-plugin'); ?></li>
                </ol>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=banner-iframe-settings'); ?>" class="button button-primary">
                        <?php _e('Configure Banner Iframe Settings', 'banner-iframe-plugin'); ?>
                    </a>
                </p>
            </div>
            
            <div class="banner-iframe-section">
                <h2><?php _e('Available Banner Locations', 'banner-iframe-plugin'); ?></h2>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e('Header (before &lt;/head&gt;)', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('Footer (before &lt;/body&gt;)', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('Within Content (with options for placement)', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('Before Sidebar', 'banner-iframe-plugin'); ?></li>
                    <li><?php _e('In Navigation Menu', 'banner-iframe-plugin'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}
