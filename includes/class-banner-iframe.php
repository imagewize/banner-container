<?php
/**
 * The main plugin class.
 *
 * @since      1.0.0
 */
class Banner_Iframe {

    /**
     * Banner locations to display iframes
     *
     * @var array
     */
    private $banner_locations = array();
    
    /**
     * The settings page instance
     *
     * @var Banner_Iframe_Settings
     */
    private $settings;

    /**
     * Initialize the plugin.
     *
     * @since    1.0.0
     */
    public function init() {
        // Initialize settings page
        $this->settings = new Banner_Iframe_Settings();
        $this->settings->init();
        
        // Register banner locations and hook displays
        $this->register_banner_locations();
        $this->hook_banner_displays();
    }

    /**
     * Register the banner locations
     */
    private function register_banner_locations() {
        $this->banner_locations = array(
            'wp_head' => __('Header (Before </head>)', 'banner-iframe-plugin'),
            'wp_footer' => __('Footer (Before </body>)', 'banner-iframe-plugin'),
            'the_content' => __('Within Content', 'banner-iframe-plugin'),
            'get_sidebar' => __('Before Sidebar', 'banner-iframe-plugin'),
            'wp_nav_menu_items' => __('In Navigation Menu', 'banner-iframe-plugin')
        );

        // Allow theme/plugins to modify available locations
        $this->banner_locations = apply_filters('banner_iframe_locations', $this->banner_locations);
    }

    /**
     * Hook the banner displays to WordPress actions
     */
    private function hook_banner_displays() {
        // Loop through each location and add the appropriate action/filter
        foreach ($this->banner_locations as $location => $label) {
            switch ($location) {
                case 'wp_head':
                    add_action('wp_head', array($this, 'display_header_banner'), 99);
                    break;
                case 'wp_footer':
                    add_action('wp_footer', array($this, 'display_footer_banner'), 10);
                    break;
                case 'the_content':
                    add_filter('the_content', array($this, 'display_content_banner'), 20);
                    break;
                case 'get_sidebar':
                    add_action('get_sidebar', array($this, 'display_sidebar_banner'), 10);
                    break;
                case 'wp_nav_menu_items':
                    add_filter('wp_nav_menu_items', array($this, 'display_menu_banner'), 10, 2);
                    break;
                default:
                    // For custom hooks
                    if (has_action($location)) {
                        add_action($location, function() use ($location) {
                            $this->display_custom_banner($location);
                        }, 10);
                    }
            }
        }
    }

    /**
     * Get available post types for selection
     */
    private function get_post_types() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $choices = array();
        
        foreach ($post_types as $post_type) {
            $choices[$post_type->name] = $post_type->label;
        }
        
        return $choices;
    }

    /**
     * Display banner in header
     */
    public function display_header_banner() {
        if (get_option('banner_wp_head_enabled')) {
            echo get_option('banner_wp_head_code', '');
        }
    }

    /**
     * Display banner in footer
     */
    public function display_footer_banner() {
        if (get_option('banner_wp_footer_enabled')) {
            echo get_option('banner_wp_footer_code', '');
        }
    }

    /**
     * Display banner in content
     */
    public function display_content_banner($content) {
        if (!is_singular() || is_feed() || is_admin()) {
            return $content;
        }

        // Check if enabled
        if (!get_option('banner_the_content_enabled')) {
            return $content;
        }

        $post_types = get_option('banner_content_post_types', array('post'));
        $current_post_type = get_post_type();
        
        if (!empty($post_types) && !in_array($current_post_type, (array) $post_types)) {
            return $content;
        }
        
        $banner_code = get_option('banner_the_content_code', '');
        $position = get_option('banner_content_position', 'top');
        
        if (empty($banner_code)) {
            return $content;
        }
        
        switch ($position) {
            case 'top':
                return $banner_code . $content;
            
            case 'bottom':
                return $content . $banner_code;
            
            case 'after_paragraph':
                $paragraph_number = (int) get_option('banner_content_paragraph', 3);
                if ($paragraph_number < 1) {
                    $paragraph_number = 1;
                }
                
                $parts = explode('</p>', $content);
                
                if (count($parts) < $paragraph_number) {
                    // Not enough paragraphs, add to the end
                    return $content . $banner_code;
                }
                
                $new_content = '';
                for ($i = 0; $i < count($parts); $i++) {
                    $new_content .= $parts[$i] . '</p>';
                    if ($i + 1 == $paragraph_number) {
                        $new_content .= $banner_code;
                    }
                }
                
                return $new_content;
                
            default:
                return $content . $banner_code;
        }
    }

    /**
     * Display banner before sidebar
     */
    public function display_sidebar_banner($name) {
        if (get_option('banner_get_sidebar_enabled')) {
            echo get_option('banner_get_sidebar_code', '');
        }
    }

    /**
     * Display banner in menu
     */
    public function display_menu_banner($items, $args) {
        if (get_option('banner_wp_nav_menu_items_enabled')) {
            $banner_code = get_option('banner_wp_nav_menu_items_code', '');
            if (!empty($banner_code)) {
                // Wrap in li for proper menu structure
                $banner_html = '<li class="menu-item banner-iframe-menu-item">' . $banner_code . '</li>';
                $items .= $banner_html;
            }
        }
        
        return $items;
    }

    /**
     * Display banner at custom location
     */
    private function display_custom_banner($location) {
        $enabled_field = 'banner_' . str_replace('-', '_', sanitize_title($location)) . '_enabled';
        $code_field = 'banner_' . str_replace('-', '_', sanitize_title($location)) . '_code';
        
        if (get_option($enabled_field)) {
            echo get_option($code_field, '');
        }
    }
}
