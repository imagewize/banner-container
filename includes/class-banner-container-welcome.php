<?php
/**
 * Banner Container Plugin - Welcome Page
 *
 * @since      1.0.0
 */

class IWZ_Banner_Container_Welcome {

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
            'iwz-banner-container-settings',
            __('Welcome to Banner Container Plugin', 'banner-container-plugin'),
            __('Welcome', 'banner-container-plugin'),
            'manage_options',
            'iwz-banner-container-welcome',
            array($this, 'display_welcome_page')
        );
    }

    /**
     * Hide the welcome page from the admin menu
     */
    public function hide_welcome_page_from_menu() {
        remove_submenu_page('iwz-banner-container-settings', 'iwz-banner-container-welcome');
    }

    /**
     * Redirect to welcome page upon plugin activation
     */
    public function redirect_to_welcome_page() {
        if (get_transient('iwz_banner_container_activation_redirect')) {
            delete_transient('iwz_banner_container_activation_redirect');
            if (!isset($_GET['activate-multi'])) {
                wp_redirect(admin_url('admin.php?page=iwz-banner-container-welcome'));
                exit;
            }
        }
    }

    /**
     * Display welcome page content
     */
    public function display_welcome_page() {
        ?>
        <div class="wrap about-wrap iwz-banner-container-welcome-wrap">
            <h1><?php _e('Welcome to Banner Container Plugin', 'banner-container-plugin'); ?></h1>
            
            <div class="about-text">
                <?php _e('Thank you for installing Banner Container Plugin! This plugin allows you to easily add banner containers to different locations in your WordPress theme.', 'banner-container-plugin'); ?>
            </div>
            
            <div class="iwz-banner-container-notice">
                <h4><?php _e('Plugin Information', 'banner-container-plugin'); ?></h4>
                <p>
                    <span class="dashicons dashicons-yes" style="color:green;"></span> 
                    <?php _e('Banner Container Plugin is ready to use!', 'banner-container-plugin'); ?>
                </p>
            </div>
            
            <div class="iwz-banner-container-section">
                <h2><?php _e('Getting Started', 'banner-container-plugin'); ?></h2>
                <p><?php _e('To configure your banner container:', 'banner-container-plugin'); ?></p>
                <ol>
                    <li><?php _e('Go to the Banner Container settings page in your WordPress admin', 'banner-container-plugin'); ?></li>
                    <li><?php _e('Enable the locations where you want to display banners', 'banner-container-plugin'); ?></li>
                    <li><?php _e('Enter your iframe or banner HTML code for each location', 'banner-container-plugin'); ?></li>
                    <li><?php _e('For content banners, choose placement options (top, bottom, or after a specific paragraph)', 'banner-container-plugin'); ?></li>
                    <li><?php _e('Save your settings', 'banner-container-plugin'); ?></li>
                </ol>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=iwz-banner-container-settings'); ?>" class="button button-primary">
                        <?php _e('Configure Banner Container Settings', 'banner-container-plugin'); ?>
                    </a>
                </p>
            </div>
            
            <div class="iwz-banner-container-section">
                <h2><?php _e('Available Banner Locations', 'banner-container-plugin'); ?></h2>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e('Header (before &lt;/head&gt;)', 'banner-container-plugin'); ?></li>
                    <li><?php _e('Footer (before &lt;/body&gt;)', 'banner-container-plugin'); ?></li>
                    <li><?php _e('Within Content (with options for placement)', 'banner-container-plugin'); ?></li>
                    <li><?php _e('Before Sidebar', 'banner-container-plugin'); ?></li>
                    <li><?php _e('In Navigation Menu', 'banner-container-plugin'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}
