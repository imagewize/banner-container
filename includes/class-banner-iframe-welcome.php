<?php
/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

/**
 * The welcome page class for the plugin.
 *
 * @since      1.0.0
 */
class Banner_Iframe_Welcome {

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
			'banner-iframe-settings',
			esc_html__( 'Welcome to Banner Iframe Plugin', 'banner-iframe-plugin' ),
			esc_html__( 'Welcome', 'banner-iframe-plugin' ),
			'manage_options',
			'banner-iframe-welcome',
			array( $this, 'display_welcome_page' )
		);
	}

	/**
	 * Hide the welcome page from the admin menu
	 */
	public function hide_welcome_page_from_menu() {
		remove_submenu_page( 'banner-iframe-settings', 'banner-iframe-welcome' );
	}

	/**
	 * Redirect to welcome page upon plugin activation
	 */
	public function redirect_to_welcome_page() {
		if ( get_transient( 'banner_iframe_activation_redirect' ) ) {
			delete_transient( 'banner_iframe_activation_redirect' );
			if ( ! isset( $_GET['activate-multi'] ) ) {
				// Add nonce check before redirecting.
				if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'banner_iframe_activation_redirect' ) ) {
					wp_die( esc_html__( 'Security check failed. Please try again.', 'banner-iframe-plugin' ) );
				}
				wp_safe_redirect( admin_url( 'admin.php?page=banner-iframe-welcome' ) );
				exit;
			}
		}
	}

	/**
	 * Process the form submission
	 */
	public function process_form() {
		// Check if form was submitted.
		if ( isset( $_POST['banner_iframe_welcome_submit'] ) ) {
			// Verify nonce for security.
			if ( ! isset( $_POST['banner_iframe_welcome_nonce'] ) ||
				! wp_verify_nonce( sanitize_key( $_POST['banner_iframe_welcome_nonce'] ), 'banner_iframe_welcome_action' ) ) {
				wp_die( esc_html__( 'Security check failed. Please try again.', 'banner-iframe-plugin' ) );
			}

			// Process form data here.
			update_option( 'banner_iframe_welcome_dismissed', true );

			// Redirect to plugin settings page.
			wp_safe_redirect( admin_url( 'admin.php?page=banner-iframe-settings' ) );
			exit;
		}
	}

	/**
	 * Display welcome page content
	 */
	public function display_welcome_page() {
		?>
		<div class="wrap about-wrap banner-iframe-welcome-wrap">
			<h1><?php esc_html_e( 'Welcome to Banner Iframe Plugin', 'banner-iframe-plugin' ); ?></h1>
			
			<div class="about-text">
				<?php esc_html_e( 'Thank you for installing Banner Iframe Plugin! This plugin allows you to easily add banner iframes to different locations in your WordPress theme.', 'banner-iframe-plugin' ); ?>
			</div>
			
			<div class="banner-iframe-notice">
				<h4><?php esc_html_e( 'Plugin Information', 'banner-iframe-plugin' ); ?></h4>
				<p>
					<span class="dashicons dashicons-yes" style="color:green;"></span> 
					<?php esc_html_e( 'Banner Iframe Plugin is ready to use!', 'banner-iframe-plugin' ); ?>
				</p>
			</div>
			
			<div class="banner-iframe-section">
				<h2><?php esc_html_e( 'Getting Started', 'banner-iframe-plugin' ); ?></h2>
				<p><?php esc_html_e( 'To configure your banner iframes:', 'banner-iframe-plugin' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Enable the locations where you want to display banners', 'banner-iframe-plugin' ); ?></li>
					<li><?php esc_html_e( 'Enter your iframe or banner HTML code for each location', 'banner-iframe-plugin' ); ?></li>
					<li><?php esc_html_e( 'For content banners, choose placement options (top, bottom, or after a specific paragraph)', 'banner-iframe-plugin' ); ?></li>
					<li><?php esc_html_e( 'Save your settings', 'banner-iframe-plugin' ); ?></li>
				</ul>
			</div>

			<div class="banner-iframe-section">
				<h2><?php esc_html_e( 'Available Banner Locations', 'banner-iframe-plugin' ); ?></h2>
				<ul>
					<li><?php esc_html_e( 'Header (before &lt;/head&gt;)', 'banner-iframe-plugin' ); ?></li>
					<li><?php esc_html_e( 'Footer (before &lt;/body&gt;)', 'banner-iframe-plugin' ); ?></li>
					<li><?php esc_html_e( 'Within Content (with options for placement)', 'banner-iframe-plugin' ); ?></li>
					<li><?php esc_html_e( 'Before Sidebar', 'banner-iframe-plugin' ); ?></li>
					<li><?php esc_html_e( 'In Navigation Menu', 'banner-iframe-plugin' ); ?></li>
				</ul>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="banner_iframe_welcome_action">
				<?php wp_nonce_field( 'banner_iframe_welcome_action', 'banner_iframe_welcome_nonce' ); ?>
				<?php submit_button( esc_html__( 'Go to Settings', 'banner-iframe-plugin' ), 'primary large', 'banner_iframe_welcome_submit' ); ?>
			</form>
		</div>
		<?php
	}
}
