<?php
/**
 * The welcome page class file.
 *
 * This file defines the IWZ_Banner_Container_Welcome class which handles
 * the plugin's welcome page functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    IWZ_Banner_Container
 * @subpackage IWZ_Banner_Container/includes
 * @author     Jasper Frumau <jasper@imagewize.com>
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Banner Container Plugin - Welcome Page
 *
 * @link       https://imagewize.com
 * @since      1.0.0
 *
 * @package    IWZ_Banner_Container
 * @subpackage IWZ_Banner_Container/includes
 */

/**
 * The welcome page class for the plugin.
 *
 * @since      1.0.0
 * @package    IWZ_Banner_Container
 * @subpackage IWZ_Banner_Container/includes
 * @author     Jasper Frumau <jasper@imagewize.com>
 */
class IWZ_Banner_Container_Welcome {

	/**
	 * Initialize the welcome page.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_welcome_page' ) );
		add_action( 'admin_head', array( $this, 'hide_welcome_page_from_menu' ) );
		add_action( 'admin_init', array( $this, 'redirect_to_welcome_page' ) );
	}

	/**
	 * Add the welcome page to admin menu
	 */
	public function add_welcome_page() {
		add_submenu_page(
			'iwz-banner-container-settings',
			__( 'Welcome to Banner Container Plugin', 'banner-container-plugin' ),
			__( 'Welcome', 'banner-container-plugin' ),
			'manage_options',
			'iwz-banner-container-welcome',
			array( $this, 'display_welcome_page' )
		);
	}

	/**
	 * Hide the welcome page from the admin menu
	 */
	public function hide_welcome_page_from_menu() {
		remove_submenu_page( 'iwz-banner-container-settings', 'iwz-banner-container-welcome' );
	}

	/**
	 * Redirect to welcome page upon plugin activation
	 */
	public function redirect_to_welcome_page() {
		if ( get_transient( 'iwz_banner_container_activation_redirect' ) ) {
			delete_transient( 'iwz_banner_container_activation_redirect' );

			// Don't redirect on bulk activation or if we're already on the welcome page.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading URL parameters to check for bulk activation, no form processing.
			if ( ! isset( $_GET['activate-multi'] ) && ! isset( $_GET['page'] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=iwz-banner-container-welcome' ) );
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
			<h1><?php esc_html_e( 'Welcome to Banner Container Plugin', 'banner-container-plugin' ); ?></h1>
			
			<div class="about-text">
				<?php esc_html_e( 'Thank you for installing Banner Container Plugin! This plugin allows you to easily add banner containers to different locations in your WordPress theme.', 'banner-container-plugin' ); ?>
			</div>
			
			<div class="iwz-banner-container-notice">
				<h4><?php esc_html_e( 'Plugin Information', 'banner-container-plugin' ); ?></h4>
				<p>
					<span class="dashicons dashicons-yes" style="color:green;"></span> 
					<?php esc_html_e( 'Banner Container Plugin is ready to use!', 'banner-container-plugin' ); ?>
				</p>
			</div>
			
			<div class="iwz-banner-container-section">
				<h2><?php esc_html_e( 'Getting Started', 'banner-container-plugin' ); ?></h2>
				<p><?php esc_html_e( 'To configure your banner container:', 'banner-container-plugin' ); ?></p>
				<ol>
					<li><?php esc_html_e( 'Go to the Banner Container settings page in your WordPress admin', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Click on any location title to expand its settings', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Enable the locations where you want to display banners', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Enter your iframe or banner HTML code for each location', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'For content banners, choose placement options (top, bottom, or after a specific paragraph)', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Save your settings', 'banner-container-plugin' ); ?></li>
				</ol>
				
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=iwz-banner-container-settings' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Configure Banner Container Settings', 'banner-container-plugin' ); ?>
					</a>
				</p>
			</div>
			
			<div class="iwz-banner-container-section">
				<h2><?php esc_html_e( 'Available Banner Locations', 'banner-container-plugin' ); ?></h2>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><?php esc_html_e( 'Header (before &lt;/head&gt;)', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Footer (before &lt;/body&gt;)', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Within Content (with options for placement)', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Before Sidebar Content (above all sidebar widgets)', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'In Navigation Menu', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Top Blabber Content Wrap', 'banner-container-plugin' ); ?></li>
					<li><?php esc_html_e( 'Blabber Footer Start (Just Above Footer Area)', 'banner-container-plugin' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}
}
