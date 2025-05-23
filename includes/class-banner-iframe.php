<?php
/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

/**
 * The main plugin class.
 *
 * @since      1.0.0
 */
class Banner_Iframe {

	/**
	 * The settings page instance
	 *
	 * @var Banner_Iframe_Settings
	 */
	private $settings;

	/**
	 * Banner locations to display iframes
	 *
	 * @var array
	 */
	private $banner_locations = array();

	/**
	 * Initialize the plugin.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		// Initialize settings page.
		$this->settings = new Banner_Iframe_Settings();
		$this->settings->init();

		// Get banner locations and hook displays.
		$this->banner_locations = $this->settings->get_banner_locations();
		$this->hook_banner_displays();
	}

	/**
	 * Hook the banner displays to WordPress actions
	 */
	private function hook_banner_displays() {
		// Loop through each location and add the appropriate action/filter.
		foreach ( $this->banner_locations as $location => $label ) {
			switch ( $location ) {
				case 'wp_head':
					add_action( 'wp_head', array( $this, 'display_header_banner' ), 99 );
					break;
				case 'wp_footer':
					add_action( 'wp_footer', array( $this, 'display_footer_banner' ), 10 );
					break;
				case 'the_content':
					add_filter( 'the_content', array( $this, 'display_content_banner' ), 20 );
					break;
				case 'get_sidebar':
					add_action( 'get_sidebar', array( $this, 'display_sidebar_banner' ), 10 );
					break;
				case 'wp_nav_menu_items':
					add_filter( 'wp_nav_menu_items', array( $this, 'display_menu_banner' ), 10, 2 );
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
	 * Display banner in header
	 */
	public function display_header_banner() {
		if ( get_option( 'banner_wp_head_enabled' ) ) {
			echo esc_html( get_option( 'banner_wp_head_code', '' ) );
		}
	}

	/**
	 * Display banner in footer
	 */
	public function display_footer_banner() {
		if ( get_option( 'banner_wp_footer_enabled' ) ) {
			echo esc_html( get_option( 'banner_wp_footer_code', '' ) );
		}
	}

	/**
	 * Display banner in content
	 *
	 * @param string $content The post content to filter.
	 * @return string Modified content with banner
	 */
	public function display_content_banner( $content ) {
		if ( ! is_singular() || is_feed() || is_admin() ) {
			return $content;
		}

		// Check if enabled.
		if ( ! get_option( 'banner_the_content_enabled' ) ) {
			return $content;
		}

		$post_types        = get_option( 'banner_content_post_types', array( 'post' ) );
		$current_post_type = get_post_type();

		// Fix the syntax error and add strict comparison.
		if ( is_array( $post_types ) && in_array( $current_post_type, $post_types, true ) ) {
			$banner_code = get_option( 'banner_the_content_code', '' );
			$position    = get_option( 'banner_content_position', 'top' );

			if ( empty( $banner_code ) ) {
				return $content;
			}

			switch ( $position ) {
				case 'top':
					return $banner_code . $content;

				case 'bottom':
					return $content . $banner_code;

				case 'after_paragraph':
					$paragraph_number = (int) get_option( 'banner_content_paragraph', 3 );
					if ( $paragraph_number < 1 ) {
						$paragraph_number = 1;
					}

					$content = $this->insert_after_paragraph( $content, $paragraph_number, $banner_code );

					return $content;

				default:
					return $content . $banner_code;
			}
		}

		return $content;
	}

	/**
	 * Insert banner after specific paragraph
	 *
	 * @param string $content    The content to modify.
	 * @param int    $paragraph  The paragraph number after which to insert the banner.
	 * @param string $insertion  The content to insert.
	 * @return string            Modified content
	 */
	private function insert_after_paragraph( $content, $paragraph, $insertion ) {
		// Find paragraphs in the content.
		$closing_p       = '</p>';
		$paragraphs      = explode( $closing_p, $content );
		$paragraph_count = count( $paragraphs );

		// Loop through paragraphs and insert banner.
		for ( $i = 0; $i < $paragraph_count; $i++ ) {
			$content .= $paragraphs[ $i ] . $closing_p;
			if ( $i + 1 === $paragraph ) {
				$content .= $insertion;
			}
		}

		return $content;
	}

	/**
	 * Display banner before sidebar
	 *
	 * @param string $name The sidebar name.
	 */
	public function display_sidebar_banner( $name ) {
		// Check if we should display for specific sidebar.
		$enabled_sidebars = get_option( 'banner_sidebar_names', array( '' ) );

		// If empty or contains empty string (all sidebars) or the specific sidebar is enabled.
		if ( get_option( 'banner_get_sidebar_enabled' ) &&
			( empty( $enabled_sidebars ) || in_array( '', $enabled_sidebars, true ) || in_array( $name, $enabled_sidebars, true ) ) ) {
			echo wp_kses_post( get_option( 'banner_get_sidebar_code', '' ) );
		}
	}

	/**
	 * Display banner in menu
	 *
	 * @param string   $items The HTML list items for the menu.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 * @return string Modified menu items
	 */
	public function display_menu_banner( $items, $args ) {
		// Check if we should display for this specific menu.
		$menu_location = isset( $args->theme_location ) ? $args->theme_location : '';
		$enabled_menus = get_option( 'banner_menu_locations', array( '' ) );

		// If empty or contains empty string (all menus) or the specific menu is enabled.
		if ( get_option( 'banner_wp_nav_menu_items_enabled' ) &&
			( empty( $enabled_menus ) || in_array( '', $enabled_menus, true ) || in_array( $menu_location, $enabled_menus, true ) ) ) {
			$banner_code = get_option( 'banner_wp_nav_menu_items_code', '' );
			if ( ! empty( $banner_code ) ) {
				// Wrap in li for proper menu structure.
				$banner_html = '<li class="menu-item banner-iframe-menu-item">' . wp_kses_post( $banner_code ) . '</li>';
				$items      .= $banner_html;
			}
		}

		return $items;
	}

	/**
	 * Get banner iframe code for specified location
	 *
	 * @param string $location Banner location key.
	 * @return string Banner iframe HTML code.
	 */
	private function get_banner_iframe( $location ) {
		// Get the banner code for the specified location.
		$option_name = 'banner_' . $location . '_code';
		$banner_code = get_option( $option_name, '' );

		return $banner_code;
	}

	/**
	 * Filter hook for modifying the_content
	 *
	 * @param string $content The post content.
	 * @return string Modified post content with banner.
	 */
	public function filter_content( $content ) {
		if ( ! is_singular() || is_feed() || is_admin() ) {
			return $content;
		}

		// Check if enabled.
		if ( ! get_option( 'banner_the_content_enabled' ) ) {
			return $content;
		}

		$post_types        = get_option( 'banner_content_post_types', array( 'post' ) );
		$current_post_type = get_post_type();

		// Fix the syntax error and add strict comparison.
		if ( is_array( $post_types ) && in_array( $current_post_type, $post_types, true ) ) {
			$banner_code = get_option( 'banner_the_content_code', '' );
			$position    = get_option( 'banner_content_position', 'top' );

			if ( empty( $banner_code ) ) {
				return $content;
			}

			switch ( $position ) {
				case 'top':
					return $banner_code . $content;

				case 'bottom':
					return $content . $banner_code;

				case 'after_paragraph':
					$paragraph_number = (int) get_option( 'banner_content_paragraph', 3 );
					if ( $paragraph_number < 1 ) {
						$paragraph_number = 1;
					}

					$content = $this->insert_after_paragraph( $content, $paragraph_number, $banner_code );

					return $content;

				default:
					return $content . $banner_code;
			}
		}

		return $content;
	}

	/**
	 * Filter hook for modifying sidebar content
	 *
	 * @param array  $args     Sidebar arguments.
	 * @param string $location Sidebar location identifier.
	 * @return void
	 */
	public function sidebar_banner( $args, $location ) {
		// Only show banner for certain sidebar locations if specified.
		$enabled_locations = get_option( 'banner_sidebar_locations', array( '' ) );

		// If empty or contains empty string (all locations) or the specific location is enabled.
		if ( get_option( 'banner_get_sidebar_enabled' ) &&
			( empty( $enabled_locations ) || in_array( '', $enabled_locations, true ) || in_array( $location, $enabled_locations, true ) ) ) {
			echo wp_kses_post( get_option( 'banner_get_sidebar_code', '' ) );
		}
	}

	/**
	 * Display banner for custom locations
	 *
	 * @param string $location Custom hook location.
	 */
	public function display_custom_banner( $location ) {
		// Check if this specific custom hook is enabled.
		$option_name = 'banner_' . $location . '_enabled';
		if ( get_option( $option_name ) ) {
			$banner_code_option = 'banner_' . $location . '_code';
			$banner_code        = get_option( $banner_code_option, '' );
			if ( ! empty( $banner_code ) ) {
				echo wp_kses_post( $banner_code );
			}
		}
	}
}
