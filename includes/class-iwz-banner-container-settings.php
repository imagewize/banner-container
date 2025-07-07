<?php
/**
 * The settings page class file.
 *
 * This file defines the IWZ_Banner_Container_Settings class which handles
 * the plugin's settings page and configuration options.
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
 * The options page for the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    IWZ_Banner_Container
 * @subpackage IWZ_Banner_Container/includes
 */

/**
 * The settings class for the plugin.
 *
 * @since      1.0.0
 * @package    IWZ_Banner_Container
 * @subpackage IWZ_Banner_Container/includes
 * @author     Jasper Frumau <jasper@imagewize.com>
 */
class IWZ_Banner_Container_Settings {

	/**
	 * Banner locations to display iframes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $banner_locations = array();

	/**
	 * Settings page hook suffix.
	 *
	 * @since 1.0.0
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

		// Add admin notices.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Register the banner locations.
	 */
	public function register_banner_locations() {
		$this->banner_locations = array(
			'the_content'            => __( 'Within Content', 'banner-container-plugin' ),
			'dynamic_sidebar_before' => __( 'Before Sidebar Content', 'banner-container-plugin' ),
			'wp_nav_menu_items'      => __( 'In Navigation Menu', 'banner-container-plugin' ),
			'content_wrap_inside'    => __( 'Inside Blabber Theme Content Wrap (Top of Content Area)', 'banner-container-plugin' ),
			'blabber_footer_start'   => __( 'Blabber Footer Start (Just Above Footer Area)', 'banner-container-plugin' ),
			'wp_head'                => __( 'Header( Top of Page) (After <body>)', 'banner-container-plugin' ),
			'wp_footer'              => __( 'Footer (Before </body>)', 'banner-container-plugin' ),
		);

		// Allow theme/plugins to modify available locations.
		$this->banner_locations = apply_filters( 'iwz_banner_container_locations', $this->banner_locations );
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
			__( 'Banner Container Settings', 'banner-container-plugin' ),
			__( 'Banner Container', 'banner-container-plugin' ),
			'manage_options',
			'iwz-banner-container-settings',
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
		wp_enqueue_style( 'iwz-banner-container-admin', IWZ_BANNER_CONTAINER_URL . 'admin/css/iwz-banner-container-admin.css', array(), IWZ_BANNER_CONTAINER_VERSION );
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		// Register a setting group for each location.
		foreach ( $this->banner_locations as $location_key => $location_label ) {
			// For head, footer, content, sidebar, navigation menu, content_wrap_inside, and blabber_footer_start banners, use the new multiple banner system.
			if ( in_array( $location_key, array( 'wp_head', 'wp_footer', 'the_content', 'dynamic_sidebar_before', 'wp_nav_menu_items', 'content_wrap_inside', 'blabber_footer_start' ), true ) ) {
				// Register setting for enabled status.
				register_setting(
					'iwz_banner_container_settings',
					'iwz_banner_' . $location_key . '_enabled',
					array(
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
						'default'           => false,
					)
				);

				// Register multiple banners setting.
				register_setting(
					'iwz_banner_container_settings',
					'iwz_banner_' . $location_key . '_banners',
					array(
						'type'              => 'array',
						'sanitize_callback' => array( $this, 'sanitize_location_banners' ),
						'default'           => array(),
					)
				);

				// For header and footer banners, add alignment and wrapper styling settings.
				if ( in_array( $location_key, array( 'wp_head', 'wp_footer' ), true ) ) {
					register_setting(
						'iwz_banner_container_settings',
						'iwz_banner_' . $location_key . '_alignment',
						array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'default'           => 'left',
						)
					);

					register_setting(
						'iwz_banner_container_settings',
						'iwz_banner_' . $location_key . '_wrapper_bg_color',
						array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_hex_color',
							'default'           => 'wp_head' === $location_key ? '#ffffff' : '#161515',
						)
					);
				}

				// Keep legacy settings for backward compatibility.
				register_setting(
					'iwz_banner_container_settings',
					'iwz_banner_' . $location_key . '_code',
					array(
						'type'              => 'string',
						'sanitize_callback' => array( $this, 'sanitize_iframe_code' ),
						'default'           => '',
					)
				);

				// For content banner, add additional legacy settings.
				if ( 'the_content' === $location_key ) {
					register_setting(
						'iwz_banner_container_settings',
						'iwz_banner_content_position',
						array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'default'           => 'top',
						)
					);

					register_setting(
						'iwz_banner_container_settings',
						'iwz_banner_content_paragraph',
						array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 3,
						)
					);

					register_setting(
						'iwz_banner_container_settings',
						'iwz_banner_content_post_types',
						array(
							'type'              => 'array',
							'sanitize_callback' => array( $this, 'sanitize_post_types' ),
							'default'           => array( 'post' ),
						)
					);
				}
			} else {
				// For other locations, use the original single banner system.
				// Register setting for enabled status.
				register_setting(
					'iwz_banner_container_settings',
					'iwz_banner_' . $location_key . '_enabled',
					array(
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
						'default'           => false,
					)
				);

				// Register setting for iframe code.
				register_setting(
					'iwz_banner_container_settings',
					'iwz_banner_' . $location_key . '_code',
					array(
						'type'              => 'string',
						'sanitize_callback' => array( $this, 'sanitize_iframe_code' ),
						'default'           => '',
					)
				);
			}
		}
	}

	/**
	 * Custom sanitization for iframe code.
	 *
	 * @param string $input The input to sanitize.
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
	 * Sanitize location banners array (for head, footer, content).
	 *
	 * @param array $input The input array to sanitize.
	 */
	public function sanitize_location_banners( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $input as $banner ) {
			if ( ! is_array( $banner ) ) {
				continue;
			}

			$sanitized_banner = array(
				'code'             => $this->sanitize_iframe_code( $banner['code'] ?? '' ),
				'device_targeting' => sanitize_text_field( $banner['device_targeting'] ?? 'all' ),
				'enabled'          => ! empty( $banner['enabled'] ),
				'wrapper_class'    => sanitize_text_field( $banner['wrapper_class'] ?? '' ),
			);

			// Add alignment for head/footer/blabber_footer_start banners.
			if ( isset( $banner['alignment'] ) ) {
				$sanitized_banner['alignment'] = sanitize_text_field( $banner['alignment'] );
			}

			// Add sticky setting for footer banners.
			if ( isset( $banner['sticky'] ) ) {
				$sanitized_banner['sticky'] = ! empty( $banner['sticky'] );
			}

			// Add background color for head/footer/blabber_footer_start banners.
			if ( isset( $banner['wrapper_bg_color'] ) ) {
				$sanitized_banner['wrapper_bg_color'] = sanitize_hex_color( $banner['wrapper_bg_color'] );
			}

			// Add margin and padding for blabber_footer_start banners.
			if ( isset( $banner['wrapper_margin'] ) ) {
				$sanitized_banner['wrapper_margin'] = sanitize_text_field( $banner['wrapper_margin'] );
			}

			if ( isset( $banner['wrapper_padding'] ) ) {
				$sanitized_banner['wrapper_padding'] = sanitize_text_field( $banner['wrapper_padding'] );
			}

			// Add content-specific fields if they exist.
			if ( isset( $banner['position'] ) ) {
				$sanitized_banner['position']   = sanitize_text_field( $banner['position'] );
				$sanitized_banner['paragraph']  = absint( $banner['paragraph'] ?? 3 );
				$sanitized_banner['post_types'] = $this->sanitize_post_types( $banner['post_types'] ?? array( 'post' ) );
			}

			// Only add non-empty banners.
			if ( ! empty( $sanitized_banner['code'] ) ) {
				$sanitized[] = $sanitized_banner;
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize content banners array (legacy method, now redirects to location banners).
	 *
	 * @param array $input The input array to sanitize.
	 */
	public function sanitize_content_banners( $input ) {
		return $this->sanitize_location_banners( $input );
	}

	/**
	 * Sanitize post types array.
	 *
	 * @param array $input The post types array to sanitize.
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
	 * Get available post types for selection.
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
			<h1><?php echo esc_html__( 'Banner Container Settings', 'banner-container-plugin' ); ?></h1>
			
			<?php settings_errors(); ?>
			
			<form method="post" action="options.php">
				<?php settings_fields( 'iwz_banner_container_settings' ); ?>
				
				<div class="iwz-banner-container-notice">
					<p><?php esc_html_e( 'Configure your banner iframe settings below. Click on a location title to expand and configure its settings.', 'banner-container-plugin' ); ?></p>
				</div>

				<?php
				// Output sections for each location.
				foreach ( $this->banner_locations as $location_key => $location_label ) :
					$enabled = get_option( 'iwz_banner_' . $location_key . '_enabled', false );
					$code    = get_option( 'iwz_banner_' . $location_key . '_code', '' );

					// Get banner count for display in title.
					$banner_count = 0;
					if ( in_array( $location_key, array( 'wp_head', 'wp_footer', 'the_content', 'dynamic_sidebar_before', 'wp_nav_menu_items', 'content_wrap_inside', 'blabber_footer_start' ), true ) ) {
						$banners      = get_option( 'iwz_banner_' . $location_key . '_banners', array() );
						$banner_count = count(
							array_filter(
								$banners,
								function ( $banner ) {
									return ! empty( $banner['enabled'] ) && ! empty( $banner['code'] );
								}
							)
						);
					} else {
						$banner_count = ( $enabled && ! empty( $code ) ) ? 1 : 0;
					}
					?>
					<div class="iwz-banner-container-location-section">
						<div class="iwz-banner-container-accordion-header" data-location="<?php echo esc_attr( $location_key ); ?>">
							<div class="iwz-banner-container-accordion-title">
								<input type="checkbox" 
									id="iwz_banner_<?php echo esc_attr( $location_key ); ?>_enabled" 
									name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_enabled" 
									value="1" 
									<?php checked( 1, $enabled ); ?>
									onclick="event.stopPropagation();" />
								<label for="iwz_banner_<?php echo esc_attr( $location_key ); ?>_enabled">
									<?php echo esc_html( $location_label ); ?>
								</label>
								<?php if ( $banner_count > 0 ) : ?>
									<span class="iwz-banner-count">
										(
										<?php
										/* translators: %d: Number of active banners */
										echo esc_html( sprintf( _n( '%d banner active', '%d banners active', $banner_count, 'banner-container-plugin' ), $banner_count ) );
										?>
										)
									</span>
								<?php endif; ?>
								<span class="iwz-banner-status-indicator <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
									<?php echo $enabled ? esc_html__( 'Enabled', 'banner-container-plugin' ) : esc_html__( 'Disabled', 'banner-container-plugin' ); ?>
								</span>
							</div>
							<span class="iwz-banner-container-accordion-toggle">▼</span>
						</div>
						
						<div class="iwz-banner-container-accordion-content" data-location="<?php echo esc_attr( $location_key ); ?>">
							<?php if ( 'the_content' === $location_key ) : ?>
								<?php
									$content_banners = get_option( 'iwz_banner_the_content_banners', array() );

									// Migrate legacy data if exists and new data is empty.
								if ( empty( $content_banners ) ) {
									$legacy_code = get_option( 'iwz_banner_the_content_code', '' );
									if ( ! empty( $legacy_code ) ) {
										$content_banners = array(
											array(
												'code'     => $legacy_code,
												'position' => get_option( 'iwz_banner_content_position', 'top' ),
												'paragraph' => get_option( 'iwz_banner_content_paragraph', 3 ),
												'post_types' => get_option( 'iwz_banner_content_post_types', array( 'post' ) ),
												'device_targeting' => 'all',
												'enabled'  => true,
											),
										);
									}
								}

									// Ensure at least one banner for new setups.
								if ( empty( $content_banners ) ) {
									$content_banners = array(
										array(
											'code'       => '',
											'position'   => 'top',
											'paragraph'  => 3,
											'post_types' => array( 'post' ),
											'device_targeting' => 'all',
											'enabled'    => false,
											'wrapper_class' => 'iwz-content-banner',
										),
									);
								}
								?>
								<table class="form-table">
									<tr>
										<td colspan="2">
											<h3><?php esc_html_e( 'Content Banners', 'banner-container-plugin' ); ?></h3>
											<p class="description">
												<?php esc_html_e( 'Add multiple banners to display within your content. Each banner can have different positioning and post type settings.', 'banner-container-plugin' ); ?>
											</p>
											
											<div id="iwz-content-banners-container">
												<?php foreach ( $content_banners as $index => $banner ) : ?>
													<div class="iwz-content-banner-item" data-index="<?php echo esc_attr( $index ); ?>">
														<div class="iwz-content-banner-header">
															<h4>
																<?php
																/* translators: %d: Banner number */
																printf( esc_html__( 'Banner %d', 'banner-container-plugin' ), esc_html( $index + 1 ) );
																?>
															</h4>
															<button type="button" class="button iwz-remove-banner" <?php echo 1 >= count( $content_banners ) ? 'style="display:none;"' : ''; ?>>
																<?php esc_html_e( 'Remove', 'banner-container-plugin' ); ?>
															</button>
														</div>
														
														<table class="form-table iwz-content-banner-settings">
														<tr>
															<th scope="row">
																<label for="iwz_content_banner_enabled_<?php echo esc_attr( $index ); ?>">
																	<?php esc_html_e( 'Enable Banner', 'banner-container-plugin' ); ?>
																</label>
															</th>
															<td>
																<input type="checkbox" 
																		id="iwz_content_banner_enabled_<?php echo esc_attr( $index ); ?>" 
																		name="iwz_banner_the_content_banners[<?php echo esc_attr( $index ); ?>][enabled]" 
																		value="1" 
																		<?php checked( ! empty( $banner['enabled'] ) ); ?> />
															</td>
														</tr>
														<tr>
															<th scope="row">
																<label for="iwz_content_banner_code_<?php echo esc_attr( $index ); ?>">
																	<?php esc_html_e( 'Banner Code', 'banner-container-plugin' ); ?>
																</label>
															</th>
															<td>
																<textarea id="iwz_content_banner_code_<?php echo esc_attr( $index ); ?>" 
																			name="iwz_banner_the_content_banners[<?php echo esc_attr( $index ); ?>][code]" 
																			rows="6" 
																			class="large-text code"><?php echo esc_textarea( $banner['code'] ?? '' ); ?></textarea>
																<p class="description">
																	<?php esc_html_e( 'Enter the iframe or banner code to insert.', 'banner-container-plugin' ); ?>
																</p>
															</td>
														</tr>
														<tr>
															<th scope="row">
																<label for="iwz_content_banner_position_<?php echo esc_attr( $index ); ?>">
																	<?php esc_html_e( 'Position', 'banner-container-plugin' ); ?>
																</label>
															</th>
															<td>
																<select id="iwz_content_banner_position_<?php echo esc_attr( $index ); ?>" 
																		name="iwz_banner_the_content_banners[<?php echo esc_attr( $index ); ?>][position]" 
																		class="iwz-banner-position-select">
																	<option value="top" <?php selected( 'top', $banner['position'] ?? 'top' ); ?>>
																		<?php esc_html_e( 'Top of content', 'banner-container-plugin' ); ?>
																	</option>
																	<option value="bottom" <?php selected( 'bottom', $banner['position'] ?? 'top' ); ?>>
																		<?php esc_html_e( 'Bottom of content', 'banner-container-plugin' ); ?>
																	</option>
																	<option value="after_paragraph" <?php selected( 'after_paragraph', $banner['position'] ?? 'top' ); ?>>
																		<?php esc_html_e( 'After specific paragraph', 'banner-container-plugin' ); ?>
																	</option>
																</select>
															</td>
														</tr>
														<tr class="iwz-paragraph-field" <?php echo ( 'after_paragraph' !== ( $banner['position'] ?? 'top' ) ) ? 'style="display:none;"' : ''; ?>>
															<th scope="row">
																<label for="iwz_content_banner_paragraph_<?php echo esc_attr( $index ); ?>">
																	<?php esc_html_e( 'Paragraph Number', 'banner-container-plugin' ); ?>
																</label>
															</th>
															<td>
																<input type="number" 
																		id="iwz_content_banner_paragraph_<?php echo esc_attr( $index ); ?>" 
																		name="iwz_banner_the_content_banners[<?php echo esc_attr( $index ); ?>][paragraph]" 
																		min="1" 
																		value="<?php echo esc_attr( $banner['paragraph'] ?? 3 ); ?>" />
																<p class="description">
																	<?php esc_html_e( 'Display after which paragraph? (Enter 1 for after first paragraph, 2 for second, etc.)', 'banner-container-plugin' ); ?>
																</p>
															</td>
														</tr>
														<tr>
															<th scope="row">
																<label for="iwz_content_banner_device_<?php echo esc_attr( $index ); ?>">
																	<?php esc_html_e( 'Device Targeting', 'banner-container-plugin' ); ?>
																</label>
															</th>
															<td>
																<select id="iwz_content_banner_device_<?php echo esc_attr( $index ); ?>" 
																		name="iwz_banner_the_content_banners[<?php echo esc_attr( $index ); ?>][device_targeting]">
																	<option value="all" <?php selected( 'all', $banner['device_targeting'] ?? 'all' ); ?>>
																		<?php esc_html_e( 'All Devices', 'banner-container-plugin' ); ?>
																	</option>
																	<option value="desktop" <?php selected( 'desktop', $banner['device_targeting'] ?? 'all' ); ?>>
																		<?php esc_html_e( 'Desktop Only', 'banner-container-plugin' ); ?>
																	</option>
																	<option value="mobile" <?php selected( 'mobile', $banner['device_targeting'] ?? 'all' ); ?>>
																		<?php esc_html_e( 'Mobile Only', 'banner-container-plugin' ); ?>
																	</option>
																</select>
																<p class="description">
																	<?php esc_html_e( 'Choose which devices should display this banner.', 'banner-container-plugin' ); ?>
																</p>
															</td>
														</tr>
														<tr>
															<th scope="row">
																<label for="iwz_content_banner_wrapper_class_<?php echo esc_attr( $index ); ?>">
																	<?php esc_html_e( 'Wrapper CSS Class', 'banner-container-plugin' ); ?>
																</label>
															</th>
															<td>
																<input type="text" 
																		id="iwz_content_banner_wrapper_class_<?php echo esc_attr( $index ); ?>" 
																		name="iwz_banner_the_content_banners[<?php echo esc_attr( $index ); ?>][wrapper_class]" 
																		value="<?php echo esc_attr( $banner['wrapper_class'] ?? '' ); ?>" 
																		class="regular-text" />
																<p class="description">
																	<?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-content-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?>
																</p>
															</td>
														</tr>
														<tr>
															<th scope="row">
																<label>
																	<?php esc_html_e( 'Apply to Post Types', 'banner-container-plugin' ); ?>
																</label>
															</th>
															<td>
																<?php
																$banner_post_types = $banner['post_types'] ?? array( 'post' );
																foreach ( $this->get_post_types() as $post_type => $post_type_label ) :
																	?>
																	<label>
																		<input type="checkbox" 
																				name="iwz_banner_the_content_banners[<?php echo esc_attr( $index ); ?>][post_types][]" 
																				value="<?php echo esc_attr( $post_type ); ?>" 
																				<?php checked( in_array( $post_type, (array) $banner_post_types, true ) ); ?> />
																		<?php echo esc_html( $post_type_label ); ?>
																	</label><br>
																<?php endforeach; ?>
																<p class="description">
																	<?php esc_html_e( 'Select which post types should display this banner.', 'banner-container-plugin' ); ?>
																</p>
															</td>
														</tr>
													</table>
												</div>
											<?php endforeach; ?>
										</div>
										
										<p>
											<button type="button" id="iwz-add-content-banner" class="button">
												<?php esc_html_e( 'Add Another Banner', 'banner-container-plugin' ); ?>
											</button>
										</p>
										</td>
									</tr>
								</table>
							
							<?php else : ?>
								<!-- Multiple banner locations (head, footer, sidebar, navigation menu, content_wrap_inside, blabber_footer_start) -->
								<?php if ( in_array( $location_key, array( 'wp_head', 'wp_footer', 'dynamic_sidebar_before', 'wp_nav_menu_items', 'content_wrap_inside', 'blabber_footer_start' ), true ) ) : ?>
									<?php
										$location_banners = get_option( 'iwz_banner_' . $location_key . '_banners', array() );

										// Migrate legacy data if exists and new data is empty.
									if ( empty( $location_banners ) ) {
										$legacy_code = get_option( 'iwz_banner_' . $location_key . '_code', '' );
										if ( ! empty( $legacy_code ) ) {
											$location_banners = array(
												array(
													'code' => $legacy_code,
													'device_targeting' => 'all',
													'enabled' => true,
												),
											);
										}
									}

										// Ensure at least one banner for new setups.
									if ( empty( $location_banners ) ) {
										$default_banner = array(
											'code'    => '',
											'device_targeting' => 'all',
											'enabled' => false,
										);

										// Add default wrapper class based on location.
										$default_wrapper_classes = array(
											'content_wrap_inside' => 'iwz-blabber-header-banner',
											'blabber_footer_start' => 'iwz-blabber-footer-banner',
											'wp_head'   => 'iwz-head-banner',
											'wp_footer' => 'iwz-footer-banner',
											'dynamic_sidebar_before' => 'iwz-sidebar-banner',
											'wp_nav_menu_items' => 'iwz-menu-banner',
										);

										if ( isset( $default_wrapper_classes[ $location_key ] ) ) {
											$default_banner['wrapper_class'] = $default_wrapper_classes[ $location_key ];
										}

										// Add default alignment for header, footer, and blabber_footer_start banners.
										if ( in_array( $location_key, array( 'wp_head', 'wp_footer', 'blabber_footer_start' ), true ) ) {
											$default_banner['alignment'] = 'left';
										}

										// Add default background color for header, footer, and blabber_footer_start banners.
										if ( in_array( $location_key, array( 'wp_head', 'wp_footer', 'blabber_footer_start' ), true ) ) {
											if ( 'wp_head' === $location_key ) {
												$default_banner['wrapper_bg_color'] = '#ffffff';
											} elseif ( 'wp_footer' === $location_key ) {
												$default_banner['wrapper_bg_color'] = '#161515';
											} else {
												$default_banner['wrapper_bg_color'] = '';
											}
										}

										// Add default margin and padding for blabber_footer_start banners.
										if ( 'blabber_footer_start' === $location_key ) {
											$default_banner['wrapper_margin']  = '';
											$default_banner['wrapper_padding'] = '';
										}

										$location_banners = array( $default_banner );
									}
									?>
									<table class="form-table">
										<?php if ( in_array( $location_key, array( 'wp_head', 'wp_footer' ), true ) ) : ?>
										<tr>
											<th scope="row">
												<label for="iwz_banner_<?php echo esc_attr( $location_key ); ?>_alignment">
													<?php esc_html_e( 'Default Banner Alignment', 'banner-container-plugin' ); ?>
												</label>
											</th>
											<td>
												<select id="iwz_banner_<?php echo esc_attr( $location_key ); ?>_alignment" 
														name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_alignment">
													<option value="left" <?php selected( 'left', get_option( 'iwz_banner_' . $location_key . '_alignment', 'left' ) ); ?>>
														<?php esc_html_e( 'Left', 'banner-container-plugin' ); ?>
													</option>
													<option value="center" <?php selected( 'center', get_option( 'iwz_banner_' . $location_key . '_alignment', 'left' ) ); ?>>
														<?php esc_html_e( 'Center', 'banner-container-plugin' ); ?>
													</option>
													<option value="right" <?php selected( 'right', get_option( 'iwz_banner_' . $location_key . '_alignment', 'left' ) ); ?>>
														<?php esc_html_e( 'Right', 'banner-container-plugin' ); ?>
													</option>
												</select>
												<p class="description">
													<?php esc_html_e( 'Default alignment for banners in this location. Individual banners can override this setting.', 'banner-container-plugin' ); ?>
												</p>
											</td>
										</tr>
										<tr>
											<th scope="row">
												<label for="iwz_banner_<?php echo esc_attr( $location_key ); ?>_wrapper_bg_color">
													<?php esc_html_e( 'Wrapper Background Color', 'banner-container-plugin' ); ?>
												</label>
											</th>
											<td>
												<input type="color" 
													id="iwz_banner_<?php echo esc_attr( $location_key ); ?>_wrapper_bg_color" 
													name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_wrapper_bg_color" 
													value="<?php echo esc_attr( get_option( 'iwz_banner_' . $location_key . '_wrapper_bg_color', ( 'wp_head' === $location_key ? '#ffffff' : '#161515' ) ) ); ?>" />
												<p class="description">
													<?php esc_html_e( 'Background color for the banner wrapper section.', 'banner-container-plugin' ); ?>
												</p>
											</td>
										</tr>
										<?php endif; ?>
										
										<tr>
											<td colspan="2">
												<?php if ( 'dynamic_sidebar_before' === $location_key ) : ?>
													<div class="iwz-banner-container-notice">
														<p><strong><?php esc_html_e( 'Note:', 'banner-container-plugin' ); ?></strong> <?php esc_html_e( 'These banners will appear before any sidebar content (widgets) loads. This ensures banners display above all other sidebar elements.', 'banner-container-plugin' ); ?></p>
													</div>
												<?php endif; ?>
												<h3>
													<?php
													/* translators: %s: Location label */
													printf( esc_html__( '%s Banners', 'banner-container-plugin' ), esc_html( $location_label ) );
													?>
												</h3>
												<p class="description">
													<?php
													/* translators: %s: Location label */
													printf( esc_html__( 'Add multiple banners to display in the %s location. Each banner can target specific devices.', 'banner-container-plugin' ), esc_html( strtolower( $location_label ) ) );
													?>
												</p>
												
												<div id="iwz-<?php echo esc_attr( $location_key ); ?>-banners-container">
													<?php foreach ( $location_banners as $index => $banner ) : ?>
														<div class="iwz-location-banner-item" data-index="<?php echo esc_attr( $index ); ?>" data-location="<?php echo esc_attr( $location_key ); ?>">
															<div class="iwz-location-banner-header">
																<h4>
																	<?php
																	/* translators: %d: Banner number */
																	printf( esc_html__( 'Banner %d', 'banner-container-plugin' ), esc_html( $index + 1 ) );
																	?>
																</h4>
																<button type="button" class="button iwz-remove-location-banner" <?php echo 1 >= count( $location_banners ) ? 'style="display:none;"' : ''; ?>>
																	<?php esc_html_e( 'Remove', 'banner-container-plugin' ); ?>
																</button>
															</div>
														
															<table class="form-table iwz-location-banner-settings">
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_enabled_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Enable Banner', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<input type="checkbox" 
																				id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_enabled_<?php echo esc_attr( $index ); ?>" 
																				name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][enabled]" 
																				value="1" 
																				<?php checked( ! empty( $banner['enabled'] ) ); ?> />
																	</td>
																</tr>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_code_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Banner Code', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<textarea id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_code_<?php echo esc_attr( $index ); ?>" 
																					name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][code]" 
																					rows="6" 
																					class="large-text code"><?php echo esc_textarea( $banner['code'] ?? '' ); ?></textarea>
																		<?php if ( 'wp_footer' === $location_key ) : ?>
																		<br><button type="button" class="button iwz-test-banner-button" data-target="iwz_<?php echo esc_attr( $location_key ); ?>_banner_code_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Insert Test Banner', 'banner-container-plugin' ); ?>
																		</button>
																		<?php endif; ?>
																		<p class="description">
																			<?php esc_html_e( 'Enter the iframe or banner code to insert.', 'banner-container-plugin' ); ?>
																		</p>
																	</td>
																</tr>
																<?php if ( in_array( $location_key, array( 'wp_head', 'wp_footer', 'blabber_footer_start' ), true ) ) : ?>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_alignment_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Banner Alignment', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<select id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_alignment_<?php echo esc_attr( $index ); ?>" 
																				name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][alignment]">
																			<option value="left" <?php selected( 'left', $banner['alignment'] ?? 'left' ); ?>>
																				<?php esc_html_e( 'Left', 'banner-container-plugin' ); ?>
																			</option>
																			<option value="center" <?php selected( 'center', $banner['alignment'] ?? 'left' ); ?>>
																				<?php esc_html_e( 'Center', 'banner-container-plugin' ); ?>
																			</option>
																			<option value="right" <?php selected( 'right', $banner['alignment'] ?? 'left' ); ?>>
																				<?php esc_html_e( 'Right', 'banner-container-plugin' ); ?>
																			</option>
																		</select>
																		<p class="description">
																			<?php esc_html_e( 'Choose the alignment for this banner.', 'banner-container-plugin' ); ?>
																		</p>
																	</td>
																</tr>
																<?php endif; ?>
																<?php if ( in_array( $location_key, array( 'wp_head', 'wp_footer', 'blabber_footer_start' ), true ) ) : ?>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_bg_color_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Wrapper Background Color', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<input type="color" 
																			id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_bg_color_<?php echo esc_attr( $index ); ?>" 
																			name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][wrapper_bg_color]" 
																			value="<?php echo esc_attr( $banner['wrapper_bg_color'] ?? ( 'wp_head' === $location_key ? '#ffffff' : ( 'wp_footer' === $location_key ? '#161515' : '' ) ) ); ?>" />
																		<p class="description">
																			<?php esc_html_e( 'Background color for this banner wrapper.', 'banner-container-plugin' ); ?>
																		</p>
																	</td>
																</tr>
																<?php endif; ?>
																<?php if ( 'blabber_footer_start' === $location_key ) : ?>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_margin_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Wrapper Margin', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<input type="text" 
																			id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_margin_<?php echo esc_attr( $index ); ?>" 
																			name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][wrapper_margin]" 
																			value="<?php echo esc_attr( $banner['wrapper_margin'] ?? '' ); ?>" 
																			class="regular-text" 
																			placeholder="e.g., 10px 0 or 1rem auto" />
																		<p class="description">
																			<?php esc_html_e( 'CSS margin for this banner wrapper. Use this to add spacing around the banner. Example: "0 20px" for left/right spacing.', 'banner-container-plugin' ); ?>
																		</p>
																	</td>
																</tr>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_padding_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Wrapper Padding', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<input type="text" 
																			id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_padding_<?php echo esc_attr( $index ); ?>" 
																			name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][wrapper_padding]" 
																			value="<?php echo esc_attr( $banner['wrapper_padding'] ?? '' ); ?>" 
																			class="regular-text" 
																			placeholder="e.g., 20px or 1rem 2rem" />
																		<p class="description">
																			<?php esc_html_e( 'CSS padding for this banner wrapper. Use this to add inner spacing within the banner wrapper.', 'banner-container-plugin' ); ?>
																		</p>
																	</td>
																</tr>
																<?php endif; ?>
																<?php if ( 'wp_footer' === $location_key ) : ?>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_sticky_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Sticky Banner', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<input type="checkbox" 
																			id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_sticky_<?php echo esc_attr( $index ); ?>" 
																			name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][sticky]" 
																			value="1" 
																			<?php checked( ! empty( $banner['sticky'] ) ); ?> />
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_sticky_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Make this banner stick to bottom of screen', 'banner-container-plugin' ); ?>
																		</label>
																		<p class="description">
																			<?php esc_html_e( 'When enabled, this banner will remain fixed at the bottom of the viewport when scrolling.', 'banner-container-plugin' ); ?>
																		</p>
																	</td>
																</tr>
																<?php endif; ?>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_device_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Device Targeting', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<select id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_device_<?php echo esc_attr( $index ); ?>" 
																				name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][device_targeting]">
																			<option value="all" <?php selected( 'all', $banner['device_targeting'] ?? 'all' ); ?>>
																				<?php esc_html_e( 'All Devices', 'banner-container-plugin' ); ?>
																			</option>
																			<option value="desktop" <?php selected( 'desktop', $banner['device_targeting'] ?? 'all' ); ?>>
																				<?php esc_html_e( 'Desktop Only', 'banner-container-plugin' ); ?>
																			</option>
																			<option value="mobile" <?php selected( 'mobile', $banner['device_targeting'] ?? 'all' ); ?>>
																				<?php esc_html_e( 'Mobile Only', 'banner-container-plugin' ); ?>
																			</option>
																		</select>
																		<p class="description">
																			<?php esc_html_e( 'Choose which devices should display this banner.', 'banner-container-plugin' ); ?>
																		</p>
																	</td>
																</tr>
																<tr>
																	<th scope="row">
																		<label for="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_class_<?php echo esc_attr( $index ); ?>">
																			<?php esc_html_e( 'Wrapper CSS Class', 'banner-container-plugin' ); ?>
																		</label>
																	</th>
																	<td>
																		<input type="text" 
																				id="iwz_<?php echo esc_attr( $location_key ); ?>_banner_wrapper_class_<?php echo esc_attr( $index ); ?>" 
																				name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_banners[<?php echo esc_attr( $index ); ?>][wrapper_class]" 
																				value="<?php echo esc_attr( $banner['wrapper_class'] ?? '' ); ?>" 
																				class="regular-text" />
																		<p class="description">
																			<?php
																			$location_descriptions = array(
																				'content_wrap_inside'    => __( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-blabber-header-banner" with predefined styling if left empty.', 'banner-container-plugin' ),
																				'blabber_footer_start'   => __( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-blabber-footer-banner" with predefined styling if left empty.', 'banner-container-plugin' ),
																				'wp_head'                => __( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-head-banner" with predefined styling if left empty.', 'banner-container-plugin' ),
																				'wp_footer'              => __( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-footer-banner" with predefined styling if left empty.', 'banner-container-plugin' ),
																				'dynamic_sidebar_before' => __( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-sidebar-banner" with predefined styling if left empty.', 'banner-container-plugin' ),
																				'wp_nav_menu_items'      => __( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-menu-banner" with predefined styling if left empty.', 'banner-container-plugin' ),
																			);

																			if ( isset( $location_descriptions[ $location_key ] ) ) {
																				echo esc_html( $location_descriptions[ $location_key ] );
																			} else {
																				esc_html_e( 'Optional CSS class(es) for the div wrapper around this banner. Leave empty for no wrapper.', 'banner-container-plugin' );
																			}
																			?>
																		</p>
																	</td>
																</tr>
															</table>
														</div>
													<?php endforeach; ?>
												</div>
												
												<p>
													<button type="button" class="iwz-add-location-banner button" data-location="<?php echo esc_attr( $location_key ); ?>">
														<?php esc_html_e( 'Add Another Banner', 'banner-container-plugin' ); ?>
													</button>
												</p>
											</td>
										</tr>
									</table>
								
								<?php else : ?>
									<!-- Standard banner locations (sidebar, menu) -->
									<table class="form-table">
										<tr>
											<th scope="row">
												<label for="iwz_banner_<?php echo esc_attr( $location_key ); ?>_code">
													<?php
													/* translators: %s: Location label */
													printf( esc_html__( '%s Banner Code', 'banner-container-plugin' ), esc_html( $location_label ) );
													?>
												</label>
											</th>
											<td>
												<textarea id="iwz_banner_<?php echo esc_attr( $location_key ); ?>_code" 
															name="iwz_banner_<?php echo esc_attr( $location_key ); ?>_code" 
															rows="6" class="large-text code"><?php echo esc_textarea( $code ); ?></textarea>
												<p class="description">
													<?php esc_html_e( 'Enter the iframe or banner code to insert at this location.', 'banner-container-plugin' ); ?>
												</p>
											</td>
										</tr>
									</table>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>

				<?php submit_button( esc_html__( 'Save Banner Settings', 'banner-container-plugin' ) ); ?>
			</form>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Test banner functionality
			$(document).on('click', '.iwz-test-banner-button', function() {
				var targetId = $(this).data('target');
				var testBanner = '<div style="background: linear-gradient(45deg, #ff6b6b, #4ecdc4); color: white; padding: 20px; text-align: center; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; border: 3px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">' +
					'🎯 TEST STICKY BANNER 🎯<br>' +
					'<span style="font-size: 14px; font-weight: normal;">This is a test banner to verify sticky footer functionality</span>' +
					'</div>';
				$('#' + targetId).val(testBanner);
			});
			
			// Helper function to get default wrapper class for a location
			function getDefaultWrapperClass(location) {
				var defaults = {
					'content_wrap_inside': 'iwz-blabber-header-banner',
					'blabber_footer_start': 'iwz-blabber-footer-banner',
					'wp_head': 'iwz-head-banner',
					'wp_footer': 'iwz-footer-banner',
					'dynamic_sidebar_before': 'iwz-sidebar-banner',
					'wp_nav_menu_items': 'iwz-menu-banner'
				};
				return defaults[location] || '';
			}
			
			// Helper function to get wrapper class description for a location
			function getWrapperClassDescription(location) {
				var descriptions = {
					'content_wrap_inside': '<?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-blabber-header-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?>',
					'blabber_footer_start': '<?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-blabber-footer-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?>',
					'wp_head': '<?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-head-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?>',
					'wp_footer': '<?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-footer-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?>',
					'dynamic_sidebar_before': '<?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-sidebar-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?>',
					'wp_nav_menu_items': '<?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-menu-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?>'
				};
				return descriptions[location] || '<?php esc_html_e( 'Optional CSS class(es) for the div wrapper around this banner. Leave empty for no wrapper.', 'banner-container-plugin' ); ?>';
			}
			
			// Accordion functionality
			$(document).on('click', '.iwz-banner-container-accordion-header', function(e) {
				// Don't toggle if clicking on checkbox or label
				if ($(e.target).is('input[type="checkbox"]') || $(e.target).is('label')) {
					e.stopPropagation();
					return;
				}
				
				var $header = $(this);
				var $content = $header.next('.iwz-banner-container-accordion-content');
				
				// Toggle this section
				$header.toggleClass('active');
				
				// Smooth animation
				if ($header.hasClass('active')) {
					$content.addClass('active').slideDown(300);
				} else {
					$content.removeClass('active').slideUp(300, function() {
						$(this).removeClass('active');
					});
				}
			});
			
			// Update status indicator when checkbox changes
			$('input[id$="_enabled"]').change(function() {
				var $header = $(this).closest('.iwz-banner-container-accordion-header');
				var $indicator = $header.find('.iwz-banner-status-indicator');
				
				if ($(this).is(':checked')) {
					$indicator.removeClass('disabled').addClass('enabled').text('<?php esc_html_e( 'Enabled', 'banner-container-plugin' ); ?>');
				} else {
					$indicator.removeClass('enabled').addClass('disabled').text('<?php esc_html_e( 'Disabled', 'banner-container-plugin' ); ?>');
				}
			});
			
			// Handle position changes for content banners.
			$(document).on('change', '.iwz-banner-position-select', function() {
				var $paragraphField = $(this).closest('.iwz-content-banner-settings').find('.iwz-paragraph-field');
				if ($(this).val() === 'after_paragraph') {
					$paragraphField.show();
				} else {
					$paragraphField.hide();
				}
			});
			
			// Update banner count in title when banners are added/removed/enabled
			function updateBannerCount(location) {
				var count = 0;
				var $container = $('#iwz-' + location + '-banners-container');
				
				if (location === 'the_content') {
					$container = $('#iwz-content-banners-container');
					$container.find('.iwz-content-banner-item').each(function() {
						var enabled = $(this).find('input[name$="[enabled]"]').is(':checked');
						var hasCode = $(this).find('textarea[name$="[code]"]').val().trim() !== '';
						if (enabled && hasCode) count++;
					});
				} else {
					$container.find('.iwz-location-banner-item').each(function() {
						var enabled = $(this).find('input[name$="[enabled]"]').is(':checked');
						var hasCode = $(this).find('textarea[name$="[code]"]').val().trim() !== '';
						if (enabled && hasCode) count++;
					});
				}
				
				var $header = $('.iwz-banner-container-accordion-header[data-location="' + location + '"]');
				var $countSpan = $header.find('.iwz-banner-count');
				
				if (count > 0) {
					var text = count === 1 ? '(1 banner active)' : '(' + count + ' banners active)';
					if ($countSpan.length) {
						$countSpan.text(text);
					} else {
						$header.find('label').after('<span class="iwz-banner-count">' + text + '</span>');
					}
				} else {
					$countSpan.remove();
				}
			}
			
			// Monitor changes to update banner counts
			$(document).on('change', 'input[name$="[enabled]"], textarea[name$="[code]"]', function() {
				var location = $(this).closest('.iwz-banner-container-accordion-content').data('location');
				if (location) {
					setTimeout(function() {
						updateBannerCount(location);
					}, 100);
				}
			});
			
			// Add new content banner.
			$('#iwz-add-content-banner').click(function() {
				var $container = $('#iwz-content-banners-container');
				var newIndex = $container.find('.iwz-content-banner-item').length;
				var postTypesHtml = '';
				
				// Generate post types checkboxes.
				<?php foreach ( $this->get_post_types() as $post_type => $post_type_label ) : ?>
				postTypesHtml += '<label><input type="checkbox" name="iwz_banner_the_content_banners[' + newIndex + '][post_types][]" value="<?php echo esc_attr( $post_type ); ?>" checked /> <?php echo esc_html( $post_type_label ); ?></label><br>';
				<?php endforeach; ?>
				
				var newBannerHtml = '<div class="iwz-content-banner-item" data-index="' + newIndex + '">' +
					'<div class="iwz-content-banner-header">' +
						'<h4><?php esc_html_e( 'Banner', 'banner-container-plugin' ); ?> ' + (newIndex + 1) + '</h4>' +
						'<button type="button" class="button iwz-remove-banner"><?php esc_html_e( 'Remove', 'banner-container-plugin' ); ?></button>' +
					'</div>' +
					'<table class="form-table iwz-content-banner-settings">' +
						'<tr>' +
							'<th scope="row"><label for="iwz_content_banner_enabled_' + newIndex + '"><?php esc_html_e( 'Enable Banner', 'banner-container-plugin' ); ?></label></th>' +
							'<td><input type="checkbox" id="iwz_content_banner_enabled_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][enabled]" value="1" /></td>' +
						'</tr>' +
						'<tr>' +
							'<th scope="row"><label for="iwz_content_banner_code_' + newIndex + '"><?php esc_html_e( 'Banner Code', 'banner-container-plugin' ); ?></label></th>' +
							'<td><textarea id="iwz_content_banner_code_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][code]" rows="6" class="large-text code"></textarea><p class="description"><?php esc_html_e( 'Enter the iframe or banner code to insert.', 'banner-container-plugin' ); ?></p></td>' +
						'</tr>' +
						'<tr>' +
							'<th scope="row"><label for="iwz_content_banner_position_' + newIndex + '"><?php esc_html_e( 'Position', 'banner-container-plugin' ); ?></label></th>' +
							'<td><select id="iwz_content_banner_position_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][position]" class="iwz-banner-position-select">' +
								'<option value="top"><?php esc_html_e( 'Top of content', 'banner-container-plugin' ); ?></option>' +
								'<option value="bottom"><?php esc_html_e( 'Bottom of content', 'banner-container-plugin' ); ?></option>' +
								'<option value="after_paragraph"><?php esc_html_e( 'After specific paragraph', 'banner-container-plugin' ); ?></option>' +
							'</select></td>' +
						'</tr>' +
						'<tr class="iwz-paragraph-field" style="display:none;">' +
							'<th scope="row"><label for="iwz_content_banner_paragraph_' + newIndex + '"><?php esc_html_e( 'Paragraph Number', 'banner-container-plugin' ); ?></label></th>' +
							'<td><input type="number" id="iwz_content_banner_paragraph_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][paragraph]" min="1" value="3" /><p class="description"><?php esc_html_e( 'Display after which paragraph? (Enter 1 for after first paragraph, 2 for second, etc.)', 'banner-container-plugin' ); ?></p></td>' +
						'</tr>' +
						'<tr>' +
							'<th scope="row"><label for="iwz_content_banner_device_' + newIndex + '"><?php esc_html_e( 'Device Targeting', 'banner-container-plugin' ); ?></label></th>' +
							'<td><select id="iwz_content_banner_device_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][device_targeting]">' +
								'<option value="all"><?php esc_html_e( 'All Devices', 'banner-container-plugin' ); ?></option>' +
								'<option value="desktop"><?php esc_html_e( 'Desktop Only', 'banner-container-plugin' ); ?></option>' +
								'<option value="mobile"><?php esc_html_e( 'Mobile Only', 'banner-container-plugin' ); ?></option>' +
							'</select><p class="description"><?php esc_html_e( 'Choose which devices should display this banner.', 'banner-container-plugin' ); ?></p></td>' +
						'</tr>' +
						'<tr>' +
							'<th scope="row"><label for="iwz_content_banner_wrapper_class_' + newIndex + '"><?php esc_html_e( 'Wrapper CSS Class', 'banner-container-plugin' ); ?></label></th>' +
							'<td><input type="text" id="iwz_content_banner_wrapper_class_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][wrapper_class]" value="iwz-content-banner" class="regular-text" /><p class="description"><?php esc_html_e( 'CSS class(es) for the div wrapper around this banner. Defaults to "iwz-content-banner" with predefined styling if left empty.', 'banner-container-plugin' ); ?></p></td>' +
						'</tr>' +
					'</table>' +
				'</div>';
				
				$container.append(newBannerHtml);
				updateRemoveButtons();
			});
			
			// Remove content banner.
			$(document).on('click', '.iwz-remove-banner', function() {
				$(this).closest('.iwz-content-banner-item').remove();
				updateBannerIndices();
				updateRemoveButtons();
			});
			
			// Add location banner (head/footer).
			$(document).on('click', '.iwz-add-location-banner', function() {
				var location = $(this).data('location');
				var $container = $('#iwz-' + location + '-banners-container');
				var newIndex = $container.find('.iwz-location-banner-item').length;
				
				var alignmentField = '';
				if (location === 'wp_head' || location === 'wp_footer' || location === 'blabber_footer_start') {
					alignmentField = '<tr>' +
						'<th scope="row"><label for="iwz_' + location + '_banner_alignment_' + newIndex + '"><?php esc_html_e( 'Banner Alignment', 'banner-container-plugin' ); ?></label></th>' +
						'<td><select id="iwz_' + location + '_banner_alignment_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][alignment]">' +
							'<option value="left"><?php esc_html_e( 'Left', 'banner-container-plugin' ); ?></option>' +
							'<option value="center"><?php esc_html_e( 'Center', 'banner-container-plugin' ); ?></option>' +
							'<option value="right"><?php esc_html_e( 'Right', 'banner-container-plugin' ); ?></option>' +
						'</select><p class="description"><?php esc_html_e( 'Choose the alignment for this banner.', 'banner-container-plugin' ); ?></p></td>' +
					'</tr>';
				}
				
				var stickyField = '';
				if (location === 'wp_footer') {
					stickyField = '<tr>' +
						'<th scope="row"><label for="iwz_' + location + '_banner_sticky_' + newIndex + '"><?php esc_html_e( 'Sticky Banner', 'banner-container-plugin' ); ?></label></th>' +
						'<td><input type="checkbox" id="iwz_' + location + '_banner_sticky_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][sticky]" value="1" />' +
						'<label for="iwz_' + location + '_banner_sticky_' + newIndex + '"><?php esc_html_e( 'Make this banner stick to bottom of screen', 'banner-container-plugin' ); ?></label>' +
						'<p class="description"><?php esc_html_e( 'When enabled, this banner will remain fixed at the bottom of the viewport when scrolling.', 'banner-container-plugin' ); ?></p></td>' +
					'</tr>';
				}
				
				var newBannerHtml = '<div class="iwz-location-banner-item" data-index="' + newIndex + '" data-location="' + location + '">' +
					'<div class="iwz-location-banner-header">' +
						'<h4><?php esc_html_e( 'Banner', 'banner-container-plugin' ); ?> ' + (newIndex + 1) + '</h4>' +
						'<button type="button" class="button iwz-remove-location-banner"><?php esc_html_e( 'Remove', 'banner-container-plugin' ); ?></button>' +
					'</div>' +
					'<table class="form-table iwz-location-banner-settings">' +
						'<tr>' +
							'<th scope="row"><label for="iwz_' + location + '_banner_enabled_' + newIndex + '"><?php esc_html_e( 'Enable Banner', 'banner-container-plugin' ); ?></label></th>' +
							'<td><input type="checkbox" id="iwz_' + location + '_banner_enabled_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][enabled]" value="1" /></td>' +
						'</tr>' +
						'<tr>' +
							'<th scope="row"><label for="iwz_' + location + '_banner_code_' + newIndex + '"><?php esc_html_e( 'Banner Code', 'banner-container-plugin' ); ?></label></th>' +
							'<td><textarea id="iwz_' + location + '_banner_code_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][code]" rows="6" class="large-text code"></textarea><p class="description"><?php esc_html_e( 'Enter the iframe or banner code to insert.', 'banner-container-plugin' ); ?></p></td>' +
						'</tr>' +
						alignmentField +
						stickyField +
						'<tr>' +
							'<th scope="row"><label for="iwz_' + location + '_banner_device_' + newIndex + '"><?php esc_html_e( 'Device Targeting', 'banner-container-plugin' ); ?></label></th>' +
							'<td><select id="iwz_' + location + '_banner_device_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][device_targeting]">' +
								'<option value="all"><?php esc_html_e( 'All Devices', 'banner-container-plugin' ); ?></option>' +
								'<option value="desktop"><?php esc_html_e( 'Desktop Only', 'banner-container-plugin' ); ?></option>' +
								'<option value="mobile"><?php esc_html_e( 'Mobile Only', 'banner-container-plugin' ); ?></option>' +
							'</select><p class="description"><?php esc_html_e( 'Choose which devices should display this banner.', 'banner-container-plugin' ); ?></p></td>' +
						'</tr>' +
						'<tr>' +
							'<th scope="row"><label for="iwz_' + location + '_banner_wrapper_class_' + newIndex + '"><?php esc_html_e( 'Wrapper CSS Class', 'banner-container-plugin' ); ?></label></th>' +
							'<td><input type="text" id="iwz_' + location + '_banner_wrapper_class_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][wrapper_class]" value="' + getDefaultWrapperClass(location) + '" class="regular-text" />' +
							'<p class="description">' + getWrapperClassDescription(location) + '</p></td>' +
						'</tr>' +
					'</table>' +
				'</div>';
				
				$container.append(newBannerHtml);
				updateLocationRemoveButtons(location);
			});
			
			// Remove location banner.
			$(document).on('click', '.iwz-remove-location-banner', function() {
				var location = $(this).closest('.iwz-location-banner-item').data('location');
				$(this).closest('.iwz-location-banner-item').remove();
				updateLocationBannerIndices(location);
				updateLocationRemoveButtons(location);
			});
			
			// Update banner indices after removal.
			function updateBannerIndices() {
				$('#iwz-content-banners-container .iwz-content-banner-item').each(function(index) {
					var $item = $(this);
					$item.attr('data-index', index);
					$item.find('h4').text('<?php esc_html_e( 'Banner', 'banner-container-plugin' ); ?> ' + (index + 1));
					
					// Update all form field names and IDs.
					$item.find('input, textarea, select').each(function() {
						var $field = $(this);
						var name = $field.attr('name');
						var id = $field.attr('id');
						
						if (name) {
							name = name.replace(/\[\d+\]/, '[' + index + ']');
							$field.attr('name', name);
						}
						
						if (id) {
							id = id.replace(/_\d+$/, '_' + index);
							$field.attr('id', id);
							$item.find('label[for="' + $field.attr('id').replace(/_\d+$/, '_' + (index + 1)) + '"]').attr('for', id);
						}
					});
				});
			}
			
			// Update location banner indices after removal.
			function updateLocationBannerIndices(location) {
				$('#iwz-' + location + '-banners-container .iwz-location-banner-item').each(function(index) {
					var $item = $(this);
					$item.attr('data-index', index);
					$item.find('h4').text('<?php esc_html_e( 'Banner', 'banner-container-plugin' ); ?> ' + (index + 1));
					
					// Update all form field names and IDs.
					$item.find('input, textarea, select').each(function() {
						var $field = $(this);
						var name = $field.attr('name');
						var id = $field.attr('id');
						
						if (name) {
							name = name.replace(/\[\d+\]/, '[' + index + ']');
							$field.attr('name', name);
						}
						
						if (id) {
							id = id.replace(/_\d+$/, '_' + index);
							$field.attr('id', id);
							$item.find('label[for="' + $field.attr('id').replace(/_\d+$/, '_' + (index + 1)) + '"]').attr('for', id);
						}
					});
				});
			}
			
			// Show/hide remove buttons.
			function updateRemoveButtons() {
				var $items = $('#iwz-content-banners-container .iwz-content-banner-item');
				if ($items.length <= 1) {
					$items.find('.iwz-remove-banner').hide();
				} else {
					$items.find('.iwz-remove-banner').show();
				}
			}
			
			// Show/hide location remove buttons.
			function updateLocationRemoveButtons(location) {
				var $items = $('#iwz-' + location + '-banners-container .iwz-location-banner-item');
				if ($items.length <= 1) {
					$items.find('.iwz-remove-location-banner').hide();
				} else {
					$items.find('.iwz-remove-location-banner').show();
				}
			}
			
			// Initialize visibility on page load.
			$('input[id$="_enabled"]').each(function() {
				var sectionContainer = $(this).closest('.iwz-banner-container-location-section');
				
				if ($(this).is(':checked')) {
					sectionContainer.find('.iwz-banner-container-code-field').removeClass('hidden').show();
				} else {
					sectionContainer.find('.iwz-banner-container-code-field').addClass('hidden').hide();
				}
			});
			
			// Initialize remove buttons for content banners.
			updateRemoveButtons();
			
			// Initialize remove buttons for location banners.
			updateLocationRemoveButtons('wp_head');
			updateLocationRemoveButtons('wp_footer');
			updateLocationRemoveButtons('dynamic_sidebar_before');
			updateLocationRemoveButtons('wp_nav_menu_items');
			updateLocationRemoveButtons('content_wrap_inside');
			updateLocationRemoveButtons('blabber_footer_start');
		});
		</script>
		<?php
	}

	/**
	 * Display admin notices.
	 */
	public function admin_notices() {
		// Only show on our settings page.
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== $this->page_hook ) {
			return;
		}

		// Check if settings were just updated.
		if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="notice notice-success is-dismissible iwz-banner-container-success">
				<p><strong><?php esc_html_e( 'Banner Container settings saved successfully!', 'banner-container-plugin' ); ?></strong></p>
			</div>
			<script>
			jQuery(document).ready(function($) {
				// Smooth scroll to top to show the notification.
				$('html, body').animate({
					scrollTop: 0
				}, 500);
				
				// Auto-dismiss the notice after 5 seconds.
				setTimeout(function() {
					$('.iwz-banner-container-success').fadeOut();
				}, 5000);
			});
			</script>
			<?php
		}
	}
}
