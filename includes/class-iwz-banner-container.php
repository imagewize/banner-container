<?php
/**
 * The main plugin class file.
 *
 * This file defines the IWZ_Banner_Container class which contains the core
 * functionality for the Banner Container plugin.
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
 * The main plugin class.
 *
 * This file contains the core functionality for the Banner Container plugin,
 * handling banner display across various WordPress locations.
 *
 * @link       https://imagewize.com
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
 * The main plugin class.
 *
 * @since      1.0.0
 */
class IWZ_Banner_Container {

	/**
	 * The settings page instance
	 *
	 * @var IWZ_Banner_Container_Settings
	 */
	private $settings;

	/**
	 * Banner locations to display iframes
	 *
	 * @var array
	 */
	private $banner_locations = array();

	/**
	 * Flag to track if header banner has been displayed
	 *
	 * @var bool
	 */
	private $header_banner_displayed = false;

	/**
	 * Check if current request is from mobile device
	 *
	 * @return bool
	 */
	private function is_mobile() {
		return wp_is_mobile();
	}

	/**
	 * Check if banner should be displayed based on device targeting.
	 *
	 * @param string $device_targeting The device targeting setting.
	 * @return bool
	 */
	private function should_display_for_device( $device_targeting ) {
		switch ( $device_targeting ) {
			case 'mobile':
				return $this->is_mobile();
			case 'desktop':
				return ! $this->is_mobile();
			case 'all':
			default:
				return true;
		}
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		// Initialize settings page.
		$this->settings = new IWZ_Banner_Container_Settings();
		$this->settings->init();

		// Get banner locations and hook displays.
		$this->banner_locations = $this->settings->get_banner_locations();
		$this->hook_banner_displays();
	}

	/**
	 * Hook the banner displays to WordPress actions.
	 */
	private function hook_banner_displays() {
		// Loop through each location and add the appropriate action/filter.
		foreach ( $this->banner_locations as $location => $label ) {
			switch ( $location ) {
				case 'wp_head':
					add_action( 'wp_body_open', array( $this, 'display_header_banner' ), 10 );
					// Fallback for themes that don't support wp_body_open.
					add_action( 'wp_head', array( $this, 'display_header_banner_fallback' ), 99 );
					break;
				case 'wp_footer':
					add_action( 'wp_footer', array( $this, 'display_footer_banner' ), 10 );
					break;
				case 'the_content':
					add_filter( 'the_content', array( $this, 'display_content_banner' ), 20 );
					break;
				case 'dynamic_sidebar_before':
					add_action( 'dynamic_sidebar_before', array( $this, 'display_sidebar_banner' ), 10 );
					break;
				case 'wp_nav_menu_items':
					add_filter( 'wp_nav_menu_items', array( $this, 'display_menu_banner' ), 10, 2 );
					break;
				case 'content_wrap_inside':
					add_action( 'wp_footer', array( $this, 'display_content_wrap_inside_banner' ), 5 );
					break;
				case 'blabber_footer_start':
					add_action( 'wp_footer', array( $this, 'display_blabber_footer_start_banner' ), 5 );
					break;
				default:
					// For custom hooks.
					if ( has_action( $location ) ) {
						add_action(
							$location,
							function () use ( $location ) {
								$this->display_custom_banner( $location );
							},
							10
						);
					}
			}
		}
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
	 * Display banner in header.
	 */
	public function display_header_banner() {
		// Prevent double display.
		if ( $this->header_banner_displayed ) {
			return;
		}

		if ( ! get_option( 'iwz_banner_wp_head_enabled' ) ) {
			return;
		}

		// Get global wrapper background color setting.
		$wrapper_bg_color = get_option( 'iwz_banner_wp_head_wrapper_bg_color', 'none' );

		// Get multiple banners.
		$banners = get_option( 'iwz_banner_wp_head_banners', array() );

		// Fallback to legacy single banner if no multiple banners exist.
		if ( empty( $banners ) ) {
			$legacy_code = get_option( 'iwz_banner_wp_head_code', '' );
			if ( ! empty( $legacy_code ) ) {
				$alignment      = get_option( 'iwz_banner_wp_head_alignment', 'left' );
				$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $legacy_code ), '', 'wp_head', $alignment, false, $wrapper_bg_color, '', '', '', 0 );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via sanitize_banner_html method
				echo $wrapped_banner;
				$this->header_banner_displayed = true;
			}
			return;
		}

		// Display enabled banners that match device targeting.
		$banner_output = '';
		$banner_index  = 0;
		foreach ( $banners as $banner ) {
			if ( ! empty( $banner['enabled'] ) && ! empty( $banner['code'] ) ) {
				$device_targeting = $banner['device_targeting'] ?? 'all';
				if ( $this->should_display_for_device( $device_targeting ) ) {
					// Use individual banner alignment if set, otherwise use global default.
					$alignment      = $banner['alignment'] ?? get_option( 'iwz_banner_wp_head_alignment', 'left' );
					$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $banner['code'] ), $banner['wrapper_class'] ?? '', 'wp_head', $alignment, false, $wrapper_bg_color, '', '', '', $banner_index );
					$banner_output .= $wrapped_banner;
					++$banner_index;
				}
			}
		}

		if ( ! empty( $banner_output ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via sanitize_banner_html method
			echo $banner_output;
			$this->header_banner_displayed = true;
		}
	}

	/**
	 * Display banner in header (fallback for themes without wp_body_open support).
	 * This outputs a script that runs after the DOM is loaded to insert the banner.
	 */
	public function display_header_banner_fallback() {
		// Only use fallback if banner hasn't been displayed yet.
		if ( $this->header_banner_displayed ) {
			return;
		}

		if ( ! get_option( 'iwz_banner_wp_head_enabled' ) ) {
			return;
		}

		// Get global wrapper background color setting.
		$wrapper_bg_color = get_option( 'iwz_banner_wp_head_wrapper_bg_color', 'none' );

		// Get multiple banners.
		$banners = get_option( 'iwz_banner_wp_head_banners', array() );

		// Fallback to legacy single banner if no multiple banners exist.
		if ( empty( $banners ) ) {
			$legacy_code = get_option( 'iwz_banner_wp_head_code', '' );
			if ( ! empty( $legacy_code ) ) {
				$alignment      = get_option( 'iwz_banner_wp_head_alignment', 'left' );
				$wrapped_banner = $this->wrap_banner_html( $legacy_code, '', 'wp_head', $alignment, false, $wrapper_bg_color, '', '', '', 0 );
				$this->output_body_banner_script( $wrapped_banner );
				$this->header_banner_displayed = true;
			}
			return;
		}

		// Display enabled banners that match device targeting.
		$banner_html  = '';
		$banner_index = 0;
		foreach ( $banners as $banner ) {
			if ( ! empty( $banner['enabled'] ) && ! empty( $banner['code'] ) ) {
				$device_targeting = $banner['device_targeting'] ?? 'all';
				if ( $this->should_display_for_device( $device_targeting ) ) {
					// Use individual banner alignment if set, otherwise use global default.
					$alignment      = $banner['alignment'] ?? get_option( 'iwz_banner_wp_head_alignment', 'left' );
					$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $banner['code'] ), $banner['wrapper_class'] ?? '', 'wp_head', $alignment, false, $wrapper_bg_color, '', '', '', $banner_index );
					$banner_html   .= $wrapped_banner;
					++$banner_index;
				}
			}
		}

		if ( ! empty( $banner_html ) ) {
			$this->output_body_banner_script( $banner_html );
			$this->header_banner_displayed = true;
		}
	}

	/**
	 * Output JavaScript to insert banner after body tag.
	 *
	 * @param string $banner_html The banner HTML to insert.
	 */
	private function output_body_banner_script( $banner_html ) {
		// Use JSON encoding to properly handle HTML content in JavaScript.
		$json_html = wp_json_encode( $banner_html );
		echo '<script type="text/javascript">';
		echo 'document.addEventListener("DOMContentLoaded", function() {';
		echo 'var bannerDiv = document.createElement("div");';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is JSON encoded and sanitized
		echo 'bannerDiv.innerHTML = ' . $json_html . ';';
		echo 'document.body.insertBefore(bannerDiv.firstChild, document.body.firstChild);';
		echo '});';
		echo '</script>';
	}

	/**
	 * Display banner in footer.
	 */
	public function display_footer_banner() {
		if ( ! get_option( 'iwz_banner_wp_footer_enabled' ) ) {
			return;
		}

		// Get global wrapper background color setting.
		$wrapper_bg_color = get_option( 'iwz_banner_wp_footer_wrapper_bg_color', 'none' );

		// Get multiple banners.
		$banners = get_option( 'iwz_banner_wp_footer_banners', array() );

		// Fallback to legacy single banner if no multiple banners exist.
		if ( empty( $banners ) ) {
			$legacy_code = get_option( 'iwz_banner_wp_footer_code', '' );
			if ( ! empty( $legacy_code ) ) {
				$alignment      = get_option( 'iwz_banner_wp_footer_alignment', 'left' );
				$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $legacy_code ), '', 'wp_footer', $alignment, false, $wrapper_bg_color, '', '', '', 0 );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via sanitize_banner_html method
				echo $wrapped_banner;
			}
			return;
		}

		// Count active banners that will be displayed to determine if we need unique IDs.
		$active_banners = array();
		foreach ( $banners as $banner ) {
			if ( ! empty( $banner['enabled'] ) && ! empty( $banner['code'] ) ) {
				$device_targeting = $banner['device_targeting'] ?? 'all';
				if ( $this->should_display_for_device( $device_targeting ) ) {
					$active_banners[] = $banner;
				}
			}
		}

		$total_active_banners = count( $active_banners );
		$banner_index         = $total_active_banners > 1 ? 1 : 0; // Start with 1 if multiple banners, 0 if single.

		// Display enabled banners that match device targeting.
		foreach ( $active_banners as $banner ) {
			// Use individual banner alignment if set, otherwise use global default.
			$alignment = $banner['alignment'] ?? get_option( 'iwz_banner_wp_footer_alignment', 'left' );
			// Use ONLY individual banner sticky setting.
			$sticky         = isset( $banner['sticky'] ) ? $banner['sticky'] : false;
			$bottom_spacing = $banner['bottom_spacing'] ?? '';

			// Add wrapper margin and padding from banner settings.
			$wrapper_margin   = $banner['wrapper_margin'] ?? '';
			$wrapper_padding  = $banner['wrapper_padding'] ?? '';
			$wrapper_bg_color = $banner['wrapper_bg_color'] ?? $wrapper_bg_color;

			$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $banner['code'] ), $banner['wrapper_class'] ?? '', 'wp_footer', $alignment, $sticky, $wrapper_bg_color, $wrapper_margin, $wrapper_padding, $bottom_spacing, $banner_index );
			// Add debug comment when sticky is enabled.
			if ( $sticky ) {
				echo '<!-- DEBUG: Footer banner with individual sticky enabled -->';
			} else {
				echo '<!-- DEBUG: Footer banner with individual sticky disabled -->';
			}
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via sanitize_banner_html method and wrapped
			echo $wrapped_banner;
			if ( $total_active_banners > 1 ) {
				++$banner_index; // Increment index for next banner only if multiple banners.
			}
		}

		// COMMENTED OUT: JavaScript solution to fix iframe alignment
		// This has been replaced with a better solution in the child theme
		// `$this->output_banner_alignment_script();`.
	}

	/**
	 * COMMENTED OUT: Output JavaScript to fix iframe inline styles that interfere with alignment.
	 * This method has been replaced with a better solution in the child theme's functions.php
	 * that prevents the theme from resizing banner iframes in the first place.
	 */

	/**
	 * Display banner in content.
	 *
	 * @param string $content The post content.
	 * @return string Modified content with banners.
	 */
	public function display_content_banner( $content ) {
		if ( ! is_singular() || is_feed() || is_admin() ) {
			return $content;
		}

		// Check if enabled.
		if ( ! get_option( 'iwz_banner_the_content_enabled' ) ) {
			return $content;
		}

		// Get content banners array.
		$content_banners = get_option( 'iwz_banner_the_content_banners', array() );

		// Legacy support - migrate old single banner if new array is empty.
		if ( empty( $content_banners ) ) {
			$legacy_code = get_option( 'iwz_banner_the_content_code', '' );
			if ( ! empty( $legacy_code ) ) {
				$content_banners = array(
					array(
						'code'             => $legacy_code,
						'position'         => get_option( 'iwz_banner_content_position', 'top' ),
						'paragraph'        => get_option( 'iwz_banner_content_paragraph', 3 ),
						'post_types'       => get_option( 'iwz_banner_content_post_types', array( 'post' ) ),
						'device_targeting' => 'all',
						'enabled'          => true,
					),
				);
			}
		}

		if ( empty( $content_banners ) ) {
			return $content;
		}

		$current_post_type = get_post_type();

		// Group banners by position for efficient processing.
		$top_banners       = array();
		$bottom_banners    = array();
		$paragraph_banners = array();

		foreach ( $content_banners as $banner ) {
			// Skip disabled banners.
			if ( empty( $banner['enabled'] ) ) {
				continue;
			}

			// Skip if banner has no code.
			if ( empty( $banner['code'] ) ) {
				continue;
			}

			// Check post type restrictions.
			$banner_post_types = $banner['post_types'] ?? array( 'post' );
			if ( ! empty( $banner_post_types ) && ! in_array( $current_post_type, (array) $banner_post_types, true ) ) {
				continue;
			}

			// Check device targeting.
			$device_targeting = $banner['device_targeting'] ?? 'all';
			if ( ! $this->should_display_for_device( $device_targeting ) ) {
				continue;
			}

			// Group by position.
			switch ( $banner['position'] ?? 'top' ) {
				case 'top':
					$top_banners[] = $this->wrap_banner_html( $banner['code'], $banner['wrapper_class'] ?? '', 'the_content', '', false, '', '', '', '', 0 );
					break;
				case 'bottom':
					$bottom_banners[] = $this->wrap_banner_html( $banner['code'], $banner['wrapper_class'] ?? '', 'the_content', '', false, '', '', '', '', 0 );
					break;
				case 'after_paragraph':
					$paragraph_number = (int) ( $banner['paragraph'] ?? 3 );
					if ( $paragraph_number < 1 ) {
						$paragraph_number = 1;
					}
					if ( ! isset( $paragraph_banners[ $paragraph_number ] ) ) {
						$paragraph_banners[ $paragraph_number ] = array();
					}
					$paragraph_banners[ $paragraph_number ][] = $this->wrap_banner_html( $banner['code'], $banner['wrapper_class'] ?? '', 'the_content', '', false, '', '', '', '', 0 );
					break;
			}
		}

		// Add top banners.
		if ( ! empty( $top_banners ) ) {
			$content = implode( '', $top_banners ) . $content;
		}

		// Add paragraph banners.
		if ( ! empty( $paragraph_banners ) ) {
			$parts       = explode( '</p>', $content );
			$new_content = '';
			$parts_count = count( $parts );

			for ( $i = 0; $i < $parts_count; $i++ ) {
				$new_content .= $parts[ $i ];
				if ( $i < $parts_count - 1 ) { // Don't add </p> to the last part.
					$new_content .= '</p>';
				}

				$paragraph_number = $i + 1;
				if ( isset( $paragraph_banners[ $paragraph_number ] ) ) {
					$new_content .= implode( '', $paragraph_banners[ $paragraph_number ] );
				}
			}

			$content = $new_content;
		}

		// Add bottom banners.
		if ( ! empty( $bottom_banners ) ) {
			$content .= implode( '', $bottom_banners );
		}

		return $content;
	}

	/**
	 * Display banner before sidebar content.
	 *
	 * @param string $index The sidebar index.
	 */
	public function display_sidebar_banner( $index ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		if ( ! get_option( 'iwz_banner_dynamic_sidebar_before_enabled' ) ) {
			return;
		}

		// Get multiple banners or fall back to legacy single banner.
		$banners = get_option( 'iwz_banner_dynamic_sidebar_before_banners', array() );

		if ( empty( $banners ) ) {
			// Check for legacy single banner.
			$legacy_code = get_option( 'iwz_banner_dynamic_sidebar_before_code', '' );
			if ( ! empty( $legacy_code ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via sanitize_banner_html method
				echo $this->sanitize_banner_html( $legacy_code );
			}
			return;
		}

		// Display multiple banners with device targeting.
		foreach ( $banners as $banner ) {
			if ( empty( $banner['enabled'] ) || empty( $banner['code'] ) ) {
				continue;
			}

			// Check device targeting.
			if ( ! $this->should_display_for_device( $banner['device_targeting'] ?? 'all' ) ) {
				continue;
			}

			$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $banner['code'] ), $banner['wrapper_class'] ?? '', 'dynamic_sidebar_before', '', false, '', '', '', '', 0 );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via sanitize_banner_html method and wrapped
			echo $wrapped_banner;
		}
	}

	/**
	 * Display banner in menu.
	 *
	 * @param string $items The menu items HTML.
	 * @param object $args The menu arguments.
	 * @return string Modified menu items with banners.
	 */
	public function display_menu_banner( $items, $args ) {
		// Prevent issues with admin menus or specific menu locations if needed.
		unset( $args ); // Acknowledge parameter to avoid phpcs warning.

		if ( ! get_option( 'iwz_banner_wp_nav_menu_items_enabled' ) ) {
			return $items;
		}

		// Get multiple banners or fall back to legacy single banner.
		$banners = get_option( 'iwz_banner_wp_nav_menu_items_banners', array() );

		if ( empty( $banners ) ) {
			// Check for legacy single banner.
			$legacy_code = get_option( 'iwz_banner_wp_nav_menu_items_code', '' );
			if ( ! empty( $legacy_code ) ) {
				// Wrap in li for proper menu structure.
				$banner_html = '<li class="menu-item iwz-banner-container-menu-item">' . $this->sanitize_banner_html( $legacy_code ) . '</li>';
				$items      .= $banner_html;
			}
			return $items;
		}

		// Display multiple banners with device targeting.
		foreach ( $banners as $banner ) {
			if ( empty( $banner['enabled'] ) || empty( $banner['code'] ) ) {
				continue;
			}

			// Check device targeting.
			if ( ! $this->should_display_for_device( $banner['device_targeting'] ?? 'all' ) ) {
				continue;
			}

			// Wrap in li for proper menu structure.
			$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $banner['code'] ), $banner['wrapper_class'] ?? '', 'wp_nav_menu_items', '', false, '', '', '', '', 0 );
			$banner_html    = '<li class="menu-item iwz-banner-container-menu-item">' . $wrapped_banner . '</li>';
			$items         .= $banner_html;
		}

		return $items;
	}

	/**
	 * Display banner inside content wrap (just after opening div.content_wrap).
	 */
	public function display_content_wrap_inside_banner() {
		if ( ! get_option( 'iwz_banner_content_wrap_inside_enabled' ) ) {
			return;
		}

		// Get multiple banners or fall back to legacy single banner.
		$banners = get_option( 'iwz_banner_content_wrap_inside_banners', array() );

		if ( empty( $banners ) ) {
			// Check for legacy single banner.
			$legacy_code = get_option( 'iwz_banner_content_wrap_inside_code', '' );
			if ( ! empty( $legacy_code ) ) {
				$this->output_content_wrap_inside_script( $legacy_code );
			}
			return;
		}

		// Display multiple banners with device targeting.
		$banner_html = '';
		foreach ( $banners as $banner ) {
			if ( empty( $banner['enabled'] ) || empty( $banner['code'] ) ) {
				continue;
			}

			// Check device targeting.
			if ( ! $this->should_display_for_device( $banner['device_targeting'] ?? 'all' ) ) {
				continue;
			}

			$wrapped_banner = $this->wrap_banner_html( $this->sanitize_banner_html( $banner['code'] ), $banner['wrapper_class'] ?? '', 'content_wrap_inside', '', false, '', '', '', '', 0 );
			$banner_html   .= $wrapped_banner;
		}

		if ( ! empty( $banner_html ) ) {
			$this->output_content_wrap_inside_script( $banner_html );
		}
	}

	/**
	 * Output JavaScript to insert banner inside content_wrap div.
	 *
	 * @param string $banner_html The banner HTML to insert.
	 */
	private function output_content_wrap_inside_script( $banner_html ) {
		// Use JSON encoding to properly handle HTML content in JavaScript.
		$json_html = wp_json_encode( $banner_html );
		echo '<script type="text/javascript">';
		echo 'document.addEventListener("DOMContentLoaded", function() {';
		echo 'var contentWrap = document.querySelector(".content_wrap");';
		echo 'if (contentWrap) {';
		echo 'var bannerDiv = document.createElement("div");';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is JSON encoded and sanitized
		echo 'bannerDiv.innerHTML = ' . $json_html . ';';
		echo 'contentWrap.insertBefore(bannerDiv, contentWrap.firstChild);';
		echo '}';
		echo '});';
		echo '</script>';
	}

	/**
	 * Display banner at the start of Blabber footer.
	 */
	/**
	 * Display banner in Blabber footer start position.
	 */
	public function display_blabber_footer_start_banner() {
		if ( ! get_option( 'iwz_banner_blabber_footer_start_enabled' ) ) {
			return;
		}

		// Get multiple banners or fall back to legacy single banner.
		$banners = get_option( 'iwz_banner_blabber_footer_start_banners', array() );

		if ( empty( $banners ) ) {
			// Check for legacy single banner.
			$legacy_code = get_option( 'iwz_banner_blabber_footer_start_code', '' );
			if ( ! empty( $legacy_code ) ) {
				// Use legacy global settings for backward compatibility.
				$wrapper_bg_color  = get_option( 'iwz_banner_blabber_footer_start_wrapper_bg_color', 'none' );
				$default_alignment = get_option( 'iwz_banner_blabber_footer_start_alignment', 'left' );
				$wrapper_margin    = get_option( 'iwz_banner_blabber_footer_start_wrapper_margin', '' );
				$wrapper_padding   = get_option( 'iwz_banner_blabber_footer_start_wrapper_padding', '' );

				$wrapped_banner = $this->wrap_banner_html(
					$this->sanitize_banner_html( $legacy_code ),
					'',
					'blabber_footer_start',
					$default_alignment,
					false,
					$wrapper_bg_color,
					$wrapper_margin,
					$wrapper_padding,
					'',
					0
				);
				$this->output_blabber_footer_start_script( $wrapped_banner );
			}
			return;
		}

		// Display multiple banners with device targeting and individual settings.
		$banner_html  = '';
		$banner_index = 1;
		foreach ( $banners as $banner ) {
			if ( empty( $banner['enabled'] ) || empty( $banner['code'] ) ) {
				continue;
			}

			// Check device targeting.
			if ( ! $this->should_display_for_device( $banner['device_targeting'] ?? 'all' ) ) {
				continue;
			}

			// Use individual banner settings with fallbacks to defaults.
			$alignment        = $banner['alignment'] ?? 'left';
			$wrapper_bg_color = $banner['wrapper_bg_color'] ?? '';
			$wrapper_margin   = $banner['wrapper_margin'] ?? '';
			$wrapper_padding  = $banner['wrapper_padding'] ?? '';

			$wrapped_banner = $this->wrap_banner_html(
				$this->sanitize_banner_html( $banner['code'] ),
				$banner['wrapper_class'] ?? '',
				'blabber_footer_start',
				$alignment,
				false,
				$wrapper_bg_color,
				$wrapper_margin,
				$wrapper_padding,
				'',
				$banner_index
			);
			$banner_html   .= $wrapped_banner;
			++$banner_index;
		}

		if ( ! empty( $banner_html ) ) {
			$this->output_blabber_footer_start_script( $banner_html );
		}
	}

	/**
	 * Output JavaScript to insert banner at the start of Blabber footer.
	 *
	 * @param string $banner_html The banner HTML to insert.
	 */
	private function output_blabber_footer_start_script( $banner_html ) {
		// Use JSON encoding to properly handle HTML content in JavaScript.
		$json_html = wp_json_encode( $banner_html );
		echo '<script type="text/javascript">';
		echo 'document.addEventListener("DOMContentLoaded", function() {';
		echo 'var footerWrap = document.querySelector("footer.footer_wrap");';
		echo 'if (footerWrap) {';
		echo 'var bannerDiv = document.createElement("div");';
		echo 'bannerDiv.className = "iwz-banner-container";';
		echo 'bannerDiv.setAttribute("data-location", "blabber_footer");';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is JSON encoded and sanitized
		echo 'bannerDiv.innerHTML = ' . $json_html . ';';
		echo 'footerWrap.parentNode.insertBefore(bannerDiv, footerWrap);';
		echo '}';
		echo '});';
		echo '</script>';
	}

	/**
	 * Display banner at custom location.
	 *
	 * @param string $location The custom location identifier.
	 */
	private function display_custom_banner( $location ) {
		$enabled_field = 'iwz_banner_' . str_replace( '-', '_', sanitize_title( $location ) ) . '_enabled';
		$code_field    = 'iwz_banner_' . str_replace( '-', '_', sanitize_title( $location ) ) . '_code';

		if ( get_option( $enabled_field ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized via sanitize_banner_html method
			echo $this->sanitize_banner_html( get_option( $code_field, '' ) );
		}
	}

	/**
	 * Sanitize banner HTML allowing iframes and other banner-related tags.
	 *
	 * @param string $html The HTML to sanitize.
	 * @return string Sanitized HTML.
	 */
	private function sanitize_banner_html( $html ) {
		$allowed_html = array(
			'iframe' => array(
				'src'         => array(),
				'width'       => array(),
				'height'      => array(),
				'frameborder' => array(),
				'scrolling'   => array(),
				'allow'       => array(),
				'title'       => array(),
				'style'       => array(),
				'class'       => array(),
				'id'          => array(),
				'data-*'      => array(),
			),
			'script' => array(
				'src'   => array(),
				'type'  => array(),
				'class' => array(),
				'id'    => array(),
				'async' => array(),
				'defer' => array(),
			),
			'div'    => array(
				'style' => array(),
				'class' => array(),
				'id'    => array(),
			),
			'a'      => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
				'class'  => array(),
				'id'     => array(),
			),
			'img'    => array(
				'src'    => array(),
				'alt'    => array(),
				'width'  => array(),
				'height' => array(),
				'class'  => array(),
				'id'     => array(),
				'style'  => array(),
			),
			'span'   => array(
				'style' => array(),
				'class' => array(),
				'id'    => array(),
			),
			'p'      => array(
				'class' => array(),
				'id'    => array(),
				'style' => array(),
			),
		);

		return wp_kses( $html, $allowed_html );
	}

	/**
	 * Wrap banner HTML in a div with custom CSS class if specified.
	 * Uses additive wrapper class system with defaults for each location.
	 *
	 * @param string $banner_html The banner HTML content.
	 * @param string $wrapper_class The CSS class for the wrapper div.
	 * @param string $location The banner location for default class determination.
	 * @param string $alignment The alignment for header/footer/blabber footer banners (left, center, right).
	 * @param bool   $sticky Whether footer banner should be sticky (footer only).
	 * @param string $wrapper_bg_color Background color for header/footer/blabber footer wrapper.
	 * @param string $wrapper_margin Custom margin for the wrapper.
	 * @param string $wrapper_padding Custom padding for the wrapper.
	 * @param string $bottom_spacing Custom bottom spacing for footer banners.
	 * @param int    $banner_index The banner index for unique ID generation to prevent conflicts.
	 * @return string Wrapped banner HTML or original HTML if no wrapper class.
	 */
	private function wrap_banner_html( $banner_html, $wrapper_class = '', $location = '', $alignment = '', $sticky = false, $wrapper_bg_color = '', $wrapper_margin = '', $wrapper_padding = '', $bottom_spacing = '', $banner_index = 0 ) {
		if ( empty( $banner_html ) ) {
			return $banner_html;
		}

		// Define default wrapper classes for each location.
		$default_wrapper_classes = array(
			'content_wrap_inside'    => 'iwz-blabber-header-banner',
			'blabber_footer_start'   => 'iwz-blabber-footer-banner',
			'wp_head'                => 'iwz-head-banner',
			'wp_footer'              => 'iwz-footer-banner',
			'dynamic_sidebar_before' => 'iwz-sidebar-banner',
			'wp_nav_menu_items'      => 'iwz-menu-banner',
			'the_content'            => 'iwz-content-banner',
		);

		// Determine classes to use (additive system).
		$classes = array();

		// Add default class if location has one.
		if ( ! empty( $location ) && isset( $default_wrapper_classes[ $location ] ) ) {
			$classes[] = $default_wrapper_classes[ $location ];
		}

		// Always add code-block class as second default for age verification support.
		$classes[] = 'code-block';

		// Add alignment class for header, footer, and blabber footer banners.
		if ( in_array( $location, array( 'wp_head', 'wp_footer', 'blabber_footer_start' ), true ) && ! empty( $alignment ) ) {
			$classes[] = 'align-' . sanitize_html_class( $alignment );

			// Add more specific class for alignment in blabber footer.
			if ( 'blabber_footer_start' === $location ) {
				$classes[] = 'iwz-align-' . sanitize_html_class( $alignment );
			}
		}

		// Add sticky class for footer banners.
		if ( 'wp_footer' === $location && $sticky ) {
			$classes[] = 'iwz-sticky';
			// Add bottom spacing class for CSS targeting.
			if ( ! empty( $bottom_spacing ) ) {
				$classes[] = 'iwz-has-bottom-spacing';
			}
		}

		// Add user-specified classes.
		if ( ! empty( $wrapper_class ) ) {
			$user_classes = explode( ' ', $wrapper_class );
			foreach ( $user_classes as $class ) {
				$class = sanitize_html_class( trim( $class ) );
				if ( ! empty( $class ) && ! in_array( $class, $classes, true ) ) {
					$classes[] = $class;
				}
			}
		}

		// If no classes at all, return unwrapped.
		if ( empty( $classes ) ) {
			return $banner_html;
		}

		// Create class string.
		$class_string = implode( ' ', array_filter( $classes ) );

		// Build wrapper style.
		$wrapper_style_parts = array();
		if ( ! empty( $wrapper_bg_color ) && 'none' !== $wrapper_bg_color ) {
			$wrapper_style_parts[] = 'background-color: ' . esc_attr( $wrapper_bg_color );
		}

		// Apply margin/padding styles.
		if ( ! empty( $wrapper_margin ) ) {
			// For sticky footer banners, convert margin to left/right positioning for fixed elements.
			if ( 'wp_footer' === $location && $sticky ) {
				// Parse margin value - could be "0 5rem" format.
				$margin_parts = explode( ' ', trim( $wrapper_margin ) );
				if ( count( $margin_parts ) >= 2 ) {
					// "0 5rem" format - use the horizontal value.
					$horizontal_margin     = $margin_parts[1];
					$wrapper_style_parts[] = 'left: ' . esc_attr( $horizontal_margin ) . ' !important';
					$wrapper_style_parts[] = 'right: ' . esc_attr( $horizontal_margin ) . ' !important';
				} elseif ( count( $margin_parts ) === 1 ) {
					// Single value - use it for all sides, but we only care about horizontal.
					$wrapper_style_parts[] = 'left: ' . esc_attr( $margin_parts[0] ) . ' !important';
					$wrapper_style_parts[] = 'right: ' . esc_attr( $margin_parts[0] ) . ' !important';
				}
			} else {
				// For non-sticky banners, use regular margin.
				$wrapper_style_parts[] = 'margin: ' . esc_attr( $wrapper_margin );
			}
		}

		if ( ! empty( $wrapper_padding ) ) {
			$wrapper_style_parts[] = 'padding: ' . esc_attr( $wrapper_padding );
		}

		if ( ! empty( $bottom_spacing ) && 'wp_footer' === $location ) {
			// Use 'bottom' property for sticky banners, 'margin-bottom' for non-sticky.
			if ( $sticky ) {
				$wrapper_style_parts[] = 'bottom: ' . esc_attr( $bottom_spacing );
			} else {
				$wrapper_style_parts[] = 'margin-bottom: ' . esc_attr( $bottom_spacing );
			}
		}

		// Special handling for Blabber Footer Start banners to prevent cut-off.
		if ( 'blabber_footer_start' === $location ) {
			// Ensure the container has sufficient height and no overflow restrictions.
			$wrapper_style_parts[] = 'overflow: visible';
			// Maintain auto height with no maximum restrictions.
			$wrapper_style_parts[] = 'height: auto';
			$wrapper_style_parts[] = 'max-height: none';
			$wrapper_style_parts[] = 'min-height: 250px'; // Minimum height to accommodate common banner sizes.
		}

		// Add wrapper div for header/footer/blabber footer with styling.
		$needs_wrapper = in_array( $location, array( 'wp_head', 'wp_footer', 'blabber_footer_start' ), true ) &&
						( ! empty( $wrapper_style_parts ) ||
							( 'wp_footer' === $location && $sticky && ! empty( $wrapper_margin ) ) );

		if ( $needs_wrapper ) {
			$wrapper_type  = 'wp_head' === $location ? 'header' : ( 'wp_footer' === $location ? 'footer' : 'blabber-footer' );
			$wrapper_style = ! empty( $wrapper_style_parts ) ? implode( '; ', $wrapper_style_parts ) . ';' : '';

			// Add sticky class to wrapper for footer banners when sticky is enabled.
			$wrapper_classes = 'iwz-banner-wrapper iwz-' . $wrapper_type . '-wrapper code-block';
			if ( 'wp_footer' === $location && $sticky ) {
				$wrapper_classes .= ' iwz-sticky-wrapper';
				// Add bottom spacing class for CSS targeting.
				if ( ! empty( $bottom_spacing ) ) {
					$wrapper_classes .= ' iwz-has-bottom-spacing';
				}
				// Add custom margin/padding classes for CSS targeting.
				if ( ! empty( $wrapper_margin ) ) {
					$wrapper_classes .= ' iwz-has-custom-margin';
					// Add override class to allow custom margin to work with sticky positioning.
					$wrapper_classes .= ' iwz-has-custom-margin-override';
				}
				if ( ! empty( $wrapper_padding ) ) {
					$wrapper_classes .= ' iwz-has-custom-padding';
				}
			}

			// Add alignment classes to wrapper for blabber footer with better specificity.
			if ( 'blabber_footer_start' === $location && ! empty( $alignment ) ) {
				$wrapper_classes .= ' iwz-wrapper-align-' . sanitize_html_class( $alignment );
				// Add uniquely named alignment class for maximum specificity.
				$wrapper_classes .= ' iwz-blabber-alignment-' . sanitize_html_class( $alignment );
			}

			// Add unique ID for multiple banners to prevent conflicts.
			$wrapper_id = '';
			if ( $banner_index > 0 ) {
				$wrapper_id = ' id="iwz-banner-' . esc_attr( $location ) . '-' . esc_attr( $banner_index ) . '"';
			} else {
				// Ensure even single banners have IDs for CSS specificity.
				$wrapper_id = ' id="iwz-banner-' . esc_attr( $location ) . '"';
			}

			// For blabber footer, create a more specific structure for better alignment control.
			if ( 'blabber_footer_start' === $location ) {
				// Create a dedicated content wrapper with specific alignment classes.
				$content_class = 'iwz-banner-content';

				// Add alignment-specific classes to content wrapper.
				if ( 'left' === $alignment ) {
					$content_class .= ' iwz-content-align-left';
					$content_style  = 'text-align: left; margin-right: auto; margin-left: 0; display: block;';
				} elseif ( 'right' === $alignment ) {
					$content_class .= ' iwz-content-align-right';
					$content_style  = 'text-align: right; margin-left: auto; margin-right: 0; display: block;';
				} else {
					$content_class .= ' iwz-content-align-center';
					$content_style  = 'text-align: center; margin: 0 auto; display: block;';
				}

				$banner_html = '<div class="' . esc_attr( $content_class ) . '" style="' . esc_attr( $content_style ) . '">' . $banner_html . '</div>';
			}

			$style_attr = ! empty( $wrapper_style ) ? ' style="' . esc_attr( $wrapper_style ) . '"' : '';
			return '<div class="' . $wrapper_classes . '"' . $wrapper_id . $style_attr . '>' .
					'<div class="' . esc_attr( $class_string ) . '">' . $banner_html . '</div>' .
					'</div>';
		}

		// For locations without wrapper styling, add unique ID for multiple banners.
		$banner_id = '';
		if ( $banner_index > 0 ) {
			$banner_id = ' id="iwz-banner-' . esc_attr( $location ) . '-' . esc_attr( $banner_index ) . '"';
		} else {
			// Ensure even single banners have IDs for CSS specificity.
			$banner_id = ' id="iwz-banner-' . esc_attr( $location ) . '"';
		}

		return '<div class="' . esc_attr( $class_string ) . '"' . $banner_id . '>' . $banner_html . '</div>';
	}
}
