<?php
/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

/**
 * The options page for the plugin.
 *
 * @since      1.0.0
 */
class Banner_Iframe_Settings {

	/**
	 * Banner locations to display iframes
	 *
	 * @var array $banner_locations Array of banner locations and labels.
	 */
	private $banner_locations = array();

	/**
	 * Settings page hook suffix
	 *
	 * @var string
	 */
	private $page_hook;

	/**
	 * Initialize the class.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		// Register settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Register banner locations.
		$this->register_banner_locations();
	}

	/**
	 * Register the banner locations
	 */
	public function register_banner_locations() {
		$this->banner_locations = array(
			'wp_head'           => esc_html__( 'Header (Before </head>)', 'banner-iframe-plugin' ),
			'wp_footer'         => esc_html__( 'Footer (Before </body>)', 'banner-iframe-plugin' ),
			'the_content'       => esc_html__( 'Within Content', 'banner-iframe-plugin' ),
			'get_sidebar'       => esc_html__( 'Before Sidebar', 'banner-iframe-plugin' ),
			'wp_nav_menu_items' => esc_html__( 'In Navigation Menu', 'banner-iframe-plugin' ),
		);

		// Allow theme/plugins to modify available locations.
		$this->banner_locations = apply_filters( 'banner_iframe_locations', $this->banner_locations );
	}

	/**
	 * Get the banner locations
	 *
	 * @return array Banner locations
	 */
	public function get_banner_locations() {
		return $this->banner_locations;
	}

	/**
	 * Add admin menu page.
	 */
	public function add_admin_menu() {
		$this->page_hook = add_menu_page(
			esc_html__( 'Banner Iframe Settings', 'banner-iframe-plugin' ),
			esc_html__( 'Banner Iframes', 'banner-iframe-plugin' ),
			'manage_options',
			'banner-iframe-settings',
			array( $this, 'display_admin_page' ),
			'dashicons-embed-generic',
			25
		);

		// Add admin styles only on our settings page.
		add_action( 'admin_print_styles-' . $this->page_hook, array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * Enqueue admin-specific styles.
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'banner-iframe-admin', BANNER_IFRAME_URL . 'admin/css/banner-iframe-admin.css', array(), BANNER_IFRAME_VERSION );
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		// Register a setting group for each location.
		foreach ( $this->banner_locations as $location_key => $location_label ) {
			// Register setting for enabled status.
			register_setting(
				'banner_iframe_settings',
				'banner_' . $location_key . '_enabled',
				array(
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
					'default'           => false,
				)
			);

			// Register setting for iframe code.
			register_setting(
				'banner_iframe_settings',
				'banner_' . $location_key . '_code',
				array(
					'type'              => 'string',
					'sanitize_callback' => array( $this, 'sanitize_iframe_code' ),
					'default'           => '',
				)
			);

			// For content banner, add additional settings.
			if ( 'the_content' === $location_key ) {
				// Position setting.
				register_setting(
					'banner_iframe_settings',
					'banner_content_position',
					array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => 'top',
					)
				);

				// Paragraph number setting.
				register_setting(
					'banner_iframe_settings',
					'banner_content_paragraph',
					array(
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'default'           => 3,
					)
				);

				// Post types setting.
				register_setting(
					'banner_iframe_settings',
					'banner_content_post_types',
					array(
						'type'              => 'array',
						'sanitize_callback' => array( $this, 'sanitize_post_types' ),
						'default'           => array( 'post' ),
					)
				);
			}
		}
	}

	/**
	 * Custom sanitization for iframe code
	 *
	 * @param string $input The input string to sanitize.
	 * @return string Sanitized input.
	 */
	public function sanitize_iframe_code( $input ) {
		// Allow iframe tags and other HTML.
		$allowed_html = array(
			'iframe' => array(
				'src'             => array(),
				'width'           => array(),
				'height'          => array(),
				'frameborder'     => array(),
				'allow'           => array(),
				'allowfullscreen' => array(),
				'scrolling'       => array(),
				'marginwidth'     => array(),
				'marginheight'    => array(),
				'style'           => array(),
				'id'              => array(),
				'class'           => array(),
				'title'           => array(),
			),
			'script' => array(
				'type'  => array(),
				'src'   => array(),
				'async' => array(),
				'defer' => array(),
				'id'    => array(),
				'class' => array(),
			),
			'div'    => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'a'      => array(
				'href'   => array(),
				'id'     => array(),
				'class'  => array(),
				'style'  => array(),
				'target' => array(),
				'rel'    => array(),
			),
			'img'    => array(
				'src'    => array(),
				'alt'    => array(),
				'id'     => array(),
				'class'  => array(),
				'style'  => array(),
				'width'  => array(),
				'height' => array(),
			),
			'span'   => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
		);

		// Use wp_kses to sanitize the HTML but allow iframes.
		return wp_kses( $input, $allowed_html );
	}

	/**
	 * Sanitize post types array
	 *
	 * @param array $input The input array to sanitize.
	 * @return array Sanitized post types array.
	 */
	public function sanitize_post_types( $input ) {
		if ( ! is_array( $input ) ) {
			return array( 'post' );
		}

		$valid_post_types = array_keys( $this->get_post_types() );

		return array_filter(
			$input,
			function ( $post_type ) use ( $valid_post_types ) {
				return in_array( $post_type, $valid_post_types, true );
			}
		);
	}

	/**
	 * Get available post types for selection
	 */
	private function get_post_types() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$choices    = array();

		foreach ( $post_types as $post_type ) {
			$choices[ $post_type->name ] = $post_type->label;
		}

		return $choices;
	}

	/**
	 * Display admin page content.
	 */
	public function display_admin_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Banner Iframe Settings', 'banner-iframe-plugin' ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'banner_iframe_settings' );
				wp_nonce_field( 'banner_iframe_settings_nonce', 'banner_iframe_nonce' );
				?>
				
				<div class="banner-iframe-notice">
					<p><?php esc_html_e( 'Configure your banner iframe settings below. Enable a location and enter the HTML code for the banner.', 'banner-iframe-plugin' ); ?></p>
				</div>

				<?php
				// Output sections for each location.
				foreach ( $this->banner_locations as $location_key => $location_label ) :
					$enabled = get_option( 'banner_' . $location_key . '_enabled', false );
					$code    = get_option( 'banner_' . $location_key . '_code', '' );
					?>
					<div class="banner-iframe-location-section">
						<h2><?php echo esc_html( $location_label ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="banner_<?php echo esc_attr( $location_key ); ?>_enabled">
										<?php
										/* translators: %s: Location label for the banner */
										printf( esc_html__( 'Enable %s Banner', 'banner-iframe-plugin' ), esc_html( $location_label ) );
										?>
									</label>
								</th>
								<td>
									<input type="checkbox" id="banner_<?php echo esc_attr( $location_key ); ?>_enabled" 
											name="banner_<?php echo esc_attr( $location_key ); ?>_enabled" 
											value="1" <?php checked( 1, $enabled ); ?> />
								</td>
							</tr>
							<tr class="banner-iframe-code-field <?php echo $enabled ? '' : 'hidden'; ?>">
								<th scope="row">
									<label for="banner_<?php echo esc_attr( $location_key ); ?>_code">
										<?php
										/* translators: %s: Location label for the banner */
										printf( esc_html__( '%s Banner Code', 'banner-iframe-plugin' ), esc_html( $location_label ) );
										?>
									</label>
								</th>
								<td>
									<textarea id="banner_<?php echo esc_attr( $location_key ); ?>_code" 
												name="banner_<?php echo esc_attr( $location_key ); ?>_code" 
												rows="6" class="large-text code"><?php echo esc_textarea( $code ); ?></textarea>
									<p class="description">
										<?php esc_html_e( 'Enter the iframe or banner code to insert at this location.', 'banner-iframe-plugin' ); ?>
									</p>
								</td>
							</tr>

							<?php if ( 'the_content' === $location_key ) : ?>
								<?php
									$position   = get_option( 'banner_content_position', 'top' );
									$paragraph  = get_option( 'banner_content_paragraph', 3 );
									$post_types = get_option( 'banner_content_post_types', array( 'post' ) );
								?>
								<tr class="banner-iframe-code-field <?php echo $enabled ? '' : 'hidden'; ?>">
									<th scope="row">
										<label for="banner_content_position">
											<?php esc_html_e( 'Content Banner Position', 'banner-iframe-plugin' ); ?>
										</label>
									</th>
									<td>
										<select id="banner_content_position" name="banner_content_position">
											<option value="top" <?php selected( 'top', $position ); ?>>
												<?php esc_html_e( 'Top of content', 'banner-iframe-plugin' ); ?>
											</option>
											<option value="bottom" <?php selected( 'bottom', $position ); ?>>
												<?php esc_html_e( 'Bottom of content', 'banner-iframe-plugin' ); ?>
											</option>
											<option value="after_paragraph" <?php selected( 'after_paragraph', $position ); ?>>
												<?php esc_html_e( 'After specific paragraph', 'banner-iframe-plugin' ); ?>
											</option>
										</select>
									</td>
								</tr>
								<tr class="banner-iframe-paragraph-field <?php echo ( $enabled && 'after_paragraph' === $position ) ? '' : 'hidden'; ?>">
									<th scope="row">
										<label for="banner_content_paragraph">
											<?php esc_html_e( 'Paragraph Number', 'banner-iframe-plugin' ); ?>
										</label>
									</th>
									<td>
										<input type="number" id="banner_content_paragraph" 
												name="banner_content_paragraph" min="1" 
												value="<?php echo esc_attr( $paragraph ); ?>" />
										<p class="description">
											<?php esc_html_e( 'Display after which paragraph? (Enter 1 for after first paragraph, 2 for second, etc.)', 'banner-iframe-plugin' ); ?>
										</p>
									</td>
								</tr>
								<tr class="banner-iframe-code-field <?php echo $enabled ? '' : 'hidden'; ?>">
									<th scope="row">
										<label for="banner_content_post_types">
											<?php esc_html_e( 'Apply to Post Types', 'banner-iframe-plugin' ); ?>
										</label>
									</th>
									<td>
										<?php foreach ( $this->get_post_types() as $post_type => $post_type_label ) : ?>
											<label>
												<input type="checkbox" 
														name="banner_content_post_types[]" 
														value="<?php echo esc_attr( $post_type ); ?>" 
														<?php checked( in_array( $post_type, (array) $post_types, true ) ); ?> />
												<?php echo esc_html( $post_type_label ); ?>
											</label><br>
										<?php endforeach; ?>
										<p class="description">
											<?php esc_html_e( 'Select which post types should display this content banner.', 'banner-iframe-plugin' ); ?>
										</p>
									</td>
								</tr>
							<?php endif; ?>
						</table>
					</div>
					<hr>
				<?php endforeach; ?>

				<?php submit_button( esc_html__( 'Save Banner Settings', 'banner-iframe-plugin' ) ); ?>
			</form>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Show/hide code field based on checkbox.
			$('input[id$="_enabled"]').change(function() {
				var location = $(this).attr('id').replace('banner_', '').replace('_enabled', '');
				if ($(this).is(':checked')) {
					$('.banner-iframe-code-field').not('.hidden').show();
					if (location === 'the_content') {
						var position = $('#banner_content_position').val();
						if (position === 'after_paragraph') {
							$('.banner-iframe-paragraph-field').show();
						}
					}
				} else {
					$('tr.banner-iframe-code-field').filter(function() {
						return $(this).find('textarea[id$="' + location + '_code"]').length > 0;
					}).hide();
					if (location === 'the_content') {
						$('.banner-iframe-paragraph-field').hide();
					}
				}
			});
			
			// Show/hide paragraph field based on position selection.
			$('#banner_content_position').change(function() {
				if ($(this).val() === 'after_paragraph') {
					$('.banner-iframe-paragraph-field').show();
				} else {
					$('.banner-iframe-paragraph-field').hide();
				}
			});
			
			// Initialize visibility.
			$('input[id$="_enabled"]').each(function() {
				if (!$(this).is(':checked')) {
					$('tr.banner-iframe-code-field').filter(function() {
						var location = $(this).find('textarea').attr('id');
						return location && location.includes($(this).attr('id').replace('banner_', '').replace('_enabled', ''));
					}).hide();
				}
			});
			
			if ($('#banner_content_position').val() !== 'after_paragraph' || !$('#banner_the_content_enabled').is(':checked')) {
				$('.banner-iframe-paragraph-field').hide();
			}
		});
		</script>
		<?php
	}

	/**
	 * Validate that the settings save request is legitimate
	 *
	 * @return boolean True if nonce is valid, false otherwise
	 */
	private function verify_nonce() {
		return isset( $_POST['banner_iframe_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['banner_iframe_nonce'] ) ), 'banner_iframe_settings_nonce' );
	}
}
