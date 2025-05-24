<?php
/**
 * The options page for the plugin.
 *
 * @since      1.0.0
 */
class IWZ_Banner_Container_Settings {

    /**
     * Banner locations to display iframes
     *
     * @var array
     */
    private $banner_locations = array();

    /**
     * Settings page hook suffix
     */
    private $page_hook;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function init() {
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register banner locations
        $this->register_banner_locations();
    }

    /**
     * Register the banner locations
     */
    public function register_banner_locations() {
        $this->banner_locations = array(
            'wp_head' => __('Top of Page (After <body>)', 'banner-container-plugin'),
            'wp_footer' => __('Footer (Before </body>)', 'banner-container-plugin'),
            'the_content' => __('Within Content', 'banner-container-plugin'),
            'get_sidebar' => __('Before Sidebar', 'banner-container-plugin'),
            'wp_nav_menu_items' => __('In Navigation Menu', 'banner-container-plugin')
        );

        // Allow theme/plugins to modify available locations
        $this->banner_locations = apply_filters('iwz_banner_container_locations', $this->banner_locations);
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
            __('Banner Container Settings', 'banner-container-plugin'),
            __('Banner Container', 'banner-container-plugin'),
            'manage_options',
            'iwz-banner-container-settings',
            array($this, 'display_admin_page'),
            'dashicons-embed-generic',
            25
        );

        // Add admin styles only on our settings page
        add_action('admin_print_styles-' . $this->page_hook, array($this, 'enqueue_admin_styles'));
    }

    /**
     * Enqueue admin-specific styles.
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style('iwz-banner-container-admin', IWZ_BANNER_CONTAINER_URL . 'admin/css/iwz-banner-container-admin.css', array(), IWZ_BANNER_CONTAINER_VERSION);
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Register a setting group for each location
        foreach ($this->banner_locations as $location_key => $location_label) {
            // For head, footer, and content banners, use the new multiple banner system
            if (in_array($location_key, array('wp_head', 'wp_footer', 'the_content'))) {
                // Register setting for enabled status
                register_setting(
                    'iwz_banner_container_settings',
                    'iwz_banner_' . $location_key . '_enabled',
                    array(
                        'type' => 'boolean',
                        'sanitize_callback' => 'rest_sanitize_boolean',
                        'default' => false,
                    )
                );

                // Register multiple banners setting
                register_setting(
                    'iwz_banner_container_settings',
                    'iwz_banner_' . $location_key . '_banners',
                    array(
                        'type' => 'array',
                        'sanitize_callback' => array($this, 'sanitize_location_banners'),
                        'default' => array(),
                    )
                );

                // Keep legacy settings for backward compatibility
                register_setting(
                    'iwz_banner_container_settings',
                    'iwz_banner_' . $location_key . '_code',
                    array(
                        'type' => 'string',
                        'sanitize_callback' => array($this, 'sanitize_iframe_code'),
                        'default' => '',
                    )
                );

                // For content banner, add additional legacy settings
                if ($location_key === 'the_content') {
                    register_setting(
                        'iwz_banner_container_settings',
                        'iwz_banner_content_position',
                        array(
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                            'default' => 'top',
                        )
                    );

                    register_setting(
                        'iwz_banner_container_settings',
                        'iwz_banner_content_paragraph',
                        array(
                            'type' => 'integer',
                            'sanitize_callback' => 'absint',
                            'default' => 3,
                        )
                    );

                    register_setting(
                        'iwz_banner_container_settings',
                        'iwz_banner_content_post_types',
                        array(
                            'type' => 'array',
                            'sanitize_callback' => array($this, 'sanitize_post_types'),
                            'default' => array('post'),
                        )
                    );
                }
            } else {
                // For other locations, use the original single banner system
                // Register setting for enabled status
                register_setting(
                    'iwz_banner_container_settings',
                    'iwz_banner_' . $location_key . '_enabled',
                    array(
                        'type' => 'boolean',
                        'sanitize_callback' => 'rest_sanitize_boolean',
                        'default' => false,
                    )
                );

                // Register setting for iframe code
                register_setting(
                    'iwz_banner_container_settings',
                    'iwz_banner_' . $location_key . '_code',
                    array(
                        'type' => 'string',
                        'sanitize_callback' => array($this, 'sanitize_iframe_code'),
                        'default' => '',
                    )
                );
            }
        }
    }

    /**
     * Custom sanitization for iframe code
     */
    public function sanitize_iframe_code($input) {
        // Allow iframe tags and other HTML
        $allowed_html = array(
            'iframe' => array(
                'src' => array(),
                'width' => array(),
                'height' => array(),
                'frameborder' => array(),
                'allow' => array(),
                'allowfullscreen' => array(),
                'scrolling' => array(),
                'marginwidth' => array(),
                'marginheight' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
                'title' => array(),
            ),
            'script' => array(
                'type' => array(),
                'src' => array(),
                'async' => array(),
                'defer' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'div' => array(
                'id' => array(),
                'class' => array(),
                'style' => array(),
            ),
            'a' => array(
                'href' => array(),
                'id' => array(),
                'class' => array(),
                'style' => array(),
                'target' => array(),
                'rel' => array(),
            ),
            'img' => array(
                'src' => array(),
                'alt' => array(),
                'id' => array(),
                'class' => array(),
                'style' => array(),
                'width' => array(),
                'height' => array(),
            ),
            'span' => array(
                'id' => array(),
                'class' => array(),
                'style' => array(),
            ),
        );

        // Use wp_kses to sanitize the HTML but allow iframes
        return wp_kses($input, $allowed_html);
    }

    /**
     * Sanitize location banners array (for head, footer, content)
     */
    public function sanitize_location_banners($input) {
        if (!is_array($input)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($input as $banner) {
            if (!is_array($banner)) {
                continue;
            }
            
            $sanitized_banner = array(
                'code' => $this->sanitize_iframe_code($banner['code'] ?? ''),
                'device_targeting' => sanitize_text_field($banner['device_targeting'] ?? 'all'),
                'enabled' => !empty($banner['enabled'])
            );
            
            // Add content-specific fields if they exist
            if (isset($banner['position'])) {
                $sanitized_banner['position'] = sanitize_text_field($banner['position']);
                $sanitized_banner['paragraph'] = absint($banner['paragraph'] ?? 3);
                $sanitized_banner['post_types'] = $this->sanitize_post_types($banner['post_types'] ?? array('post'));
            }
            
            // Only add non-empty banners
            if (!empty($sanitized_banner['code'])) {
                $sanitized[] = $sanitized_banner;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize content banners array (legacy method, now redirects to location banners)
     */
    public function sanitize_content_banners($input) {
        return $this->sanitize_location_banners($input);
    }

    /**
     * Sanitize post types array
     */
    public function sanitize_post_types($input) {
        if (!is_array($input)) {
            return array('post');
        }
        
        $valid_post_types = array_keys($this->get_post_types());
        
        return array_filter($input, function($post_type) use ($valid_post_types) {
            return in_array($post_type, $valid_post_types);
        });
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
     * Display admin page content.
     */
    public function display_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Banner Container Settings', 'banner-container-plugin'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('iwz_banner_container_settings'); ?>
                
                <div class="iwz-banner-container-notice">
                    <p><?php _e('Configure your banner iframe settings below. Enable a location and enter the HTML code for the banner.', 'banner-container-plugin'); ?></p>
                </div>

                <?php
                // Output sections for each location
                foreach ($this->banner_locations as $location_key => $location_label) :
                    $enabled = get_option('iwz_banner_' . $location_key . '_enabled', false);
                    $code = get_option('iwz_banner_' . $location_key . '_code', '');
                ?>
                    <div class="iwz-banner-container-location-section">
                        <h2><?php echo esc_html($location_label); ?></h2>
                        
                        <?php if ($location_key === 'the_content') : ?>
                            <?php 
                                $content_banners = get_option('iwz_banner_the_content_banners', array());
                                
                                // Migrate legacy data if exists and new data is empty
                                if (empty($content_banners)) {
                                    $legacy_code = get_option('iwz_banner_the_content_code', '');
                                    if (!empty($legacy_code)) {
                                        $content_banners = array(array(
                                            'code' => $legacy_code,
                                            'position' => get_option('iwz_banner_content_position', 'top'),
                                            'paragraph' => get_option('iwz_banner_content_paragraph', 3),
                                            'post_types' => get_option('iwz_banner_content_post_types', array('post')),
                                            'device_targeting' => 'all',
                                            'enabled' => true
                                        ));
                                    }
                                }
                                
                                // Ensure at least one banner for new setups
                                if (empty($content_banners)) {
                                    $content_banners = array(array(
                                        'code' => '',
                                        'position' => 'top',
                                        'paragraph' => 3,
                                        'post_types' => array('post'),
                                        'device_targeting' => 'all',
                                        'enabled' => false
                                    ));
                                }
                            ?>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled">
                                            <?php printf(__('Enable %s Banner', 'banner-container-plugin'), esc_html($location_label)); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled" 
                                               name="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled" 
                                               value="1" <?php checked(1, $enabled); ?> />
                                    </td>
                                </tr>
                                <tr class="iwz-banner-container-code-field">
                                    <td colspan="2">
                                        <h3><?php _e('Content Banners', 'banner-container-plugin'); ?></h3>
                                        <p class="description">
                                            <?php _e('Add multiple banners to display within your content. Each banner can have different positioning and post type settings.', 'banner-container-plugin'); ?>
                                        </p>
                                        
                                        <div id="iwz-content-banners-container">
                                            <?php foreach ($content_banners as $index => $banner) : ?>
                                                <div class="iwz-content-banner-item" data-index="<?php echo $index; ?>">
                                                    <div class="iwz-content-banner-header">
                                                        <h4><?php printf(__('Banner %d', 'banner-container-plugin'), $index + 1); ?></h4>
                                                        <button type="button" class="button iwz-remove-banner" <?php echo count($content_banners) <= 1 ? 'style="display:none;"' : ''; ?>>
                                                            <?php _e('Remove', 'banner-container-plugin'); ?>
                                                        </button>
                                                    </div>
                                                    
                                                    <table class="form-table iwz-content-banner-settings">
                                                        <tr>
                                                            <th scope="row">
                                                                <label for="iwz_content_banner_enabled_<?php echo $index; ?>">
                                                                    <?php _e('Enable Banner', 'banner-container-plugin'); ?>
                                                                </label>
                                                            </th>
                                                            <td>
                                                                <input type="checkbox" 
                                                                       id="iwz_content_banner_enabled_<?php echo $index; ?>" 
                                                                       name="iwz_banner_the_content_banners[<?php echo $index; ?>][enabled]" 
                                                                       value="1" 
                                                                       <?php checked(!empty($banner['enabled'])); ?> />
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">
                                                                <label for="iwz_content_banner_code_<?php echo $index; ?>">
                                                                    <?php _e('Banner Code', 'banner-container-plugin'); ?>
                                                                </label>
                                                            </th>
                                                            <td>
                                                                <textarea id="iwz_content_banner_code_<?php echo $index; ?>" 
                                                                          name="iwz_banner_the_content_banners[<?php echo $index; ?>][code]" 
                                                                          rows="6" 
                                                                          class="large-text code"><?php echo esc_textarea($banner['code'] ?? ''); ?></textarea>
                                                                <p class="description">
                                                                    <?php _e('Enter the iframe or banner code to insert.', 'banner-container-plugin'); ?>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">
                                                                <label for="iwz_content_banner_position_<?php echo $index; ?>">
                                                                    <?php _e('Position', 'banner-container-plugin'); ?>
                                                                </label>
                                                            </th>
                                                            <td>
                                                                <select id="iwz_content_banner_position_<?php echo $index; ?>" 
                                                                        name="iwz_banner_the_content_banners[<?php echo $index; ?>][position]" 
                                                                        class="iwz-banner-position-select">
                                                                    <option value="top" <?php selected('top', $banner['position'] ?? 'top'); ?>>
                                                                        <?php _e('Top of content', 'banner-container-plugin'); ?>
                                                                    </option>
                                                                    <option value="bottom" <?php selected('bottom', $banner['position'] ?? 'top'); ?>>
                                                                        <?php _e('Bottom of content', 'banner-container-plugin'); ?>
                                                                    </option>
                                                                    <option value="after_paragraph" <?php selected('after_paragraph', $banner['position'] ?? 'top'); ?>>
                                                                        <?php _e('After specific paragraph', 'banner-container-plugin'); ?>
                                                                    </option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr class="iwz-paragraph-field" <?php echo ($banner['position'] ?? 'top') !== 'after_paragraph' ? 'style="display:none;"' : ''; ?>>
                                                            <th scope="row">
                                                                <label for="iwz_content_banner_paragraph_<?php echo $index; ?>">
                                                                    <?php _e('Paragraph Number', 'banner-container-plugin'); ?>
                                                                </label>
                                                            </th>
                                                            <td>
                                                                <input type="number" 
                                                                       id="iwz_content_banner_paragraph_<?php echo $index; ?>" 
                                                                       name="iwz_banner_the_content_banners[<?php echo $index; ?>][paragraph]" 
                                                                       min="1" 
                                                                       value="<?php echo esc_attr($banner['paragraph'] ?? 3); ?>" />
                                                                <p class="description">
                                                                    <?php _e('Display after which paragraph? (Enter 1 for after first paragraph, 2 for second, etc.)', 'banner-container-plugin'); ?>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">
                                                                <label for="iwz_content_banner_device_<?php echo $index; ?>">
                                                                    <?php _e('Device Targeting', 'banner-container-plugin'); ?>
                                                                </label>
                                                            </th>
                                                            <td>
                                                                <select id="iwz_content_banner_device_<?php echo $index; ?>" 
                                                                        name="iwz_banner_the_content_banners[<?php echo $index; ?>][device_targeting]">
                                                                    <option value="all" <?php selected('all', $banner['device_targeting'] ?? 'all'); ?>>
                                                                        <?php _e('All Devices', 'banner-container-plugin'); ?>
                                                                    </option>
                                                                    <option value="desktop" <?php selected('desktop', $banner['device_targeting'] ?? 'all'); ?>>
                                                                        <?php _e('Desktop Only', 'banner-container-plugin'); ?>
                                                                    </option>
                                                                    <option value="mobile" <?php selected('mobile', $banner['device_targeting'] ?? 'all'); ?>>
                                                                        <?php _e('Mobile Only', 'banner-container-plugin'); ?>
                                                                    </option>
                                                                </select>
                                                                <p class="description">
                                                                    <?php _e('Choose which devices should display this banner.', 'banner-container-plugin'); ?>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">
                                                                <label>
                                                                    <?php _e('Apply to Post Types', 'banner-container-plugin'); ?>
                                                                </label>
                                                            </th>
                                                            <td>
                                                                <?php 
                                                                $banner_post_types = $banner['post_types'] ?? array('post');
                                                                foreach ($this->get_post_types() as $post_type => $post_type_label) : ?>
                                                                    <label>
                                                                        <input type="checkbox" 
                                                                               name="iwz_banner_the_content_banners[<?php echo $index; ?>][post_types][]" 
                                                                               value="<?php echo esc_attr($post_type); ?>" 
                                                                               <?php checked(in_array($post_type, (array) $banner_post_types)); ?> />
                                                                        <?php echo esc_html($post_type_label); ?>
                                                                    </label><br>
                                                                <?php endforeach; ?>
                                                                <p class="description">
                                                                    <?php _e('Select which post types should display this banner.', 'banner-container-plugin'); ?>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <p>
                                            <button type="button" id="iwz-add-content-banner" class="button">
                                                <?php _e('Add Another Banner', 'banner-container-plugin'); ?>
                                            </button>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        
                        <?php else : ?>
                            <!-- Multiple banner locations (head, footer) -->
                            <?php if (in_array($location_key, array('wp_head', 'wp_footer'))) : ?>
                                <?php 
                                    $location_banners = get_option('iwz_banner_' . $location_key . '_banners', array());
                                    
                                    // Migrate legacy data if exists and new data is empty
                                    if (empty($location_banners)) {
                                        $legacy_code = get_option('iwz_banner_' . $location_key . '_code', '');
                                        if (!empty($legacy_code)) {
                                            $location_banners = array(array(
                                                'code' => $legacy_code,
                                                'device_targeting' => 'all',
                                                'enabled' => true
                                            ));
                                        }
                                    }
                                    
                                    // Ensure at least one banner for new setups
                                    if (empty($location_banners)) {
                                        $location_banners = array(array(
                                            'code' => '',
                                            'device_targeting' => 'all',
                                            'enabled' => false
                                        ));
                                    }
                                ?>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled">
                                                <?php printf(__('Enable %s Banner', 'banner-container-plugin'), esc_html($location_label)); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="checkbox" id="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled" 
                                                   name="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled" 
                                                   value="1" <?php checked(1, $enabled); ?> />
                                        </td>
                                    </tr>
                                    <tr class="iwz-banner-container-code-field">
                                        <td colspan="2">
                                            <h3><?php printf(__('%s Banners', 'banner-container-plugin'), esc_html($location_label)); ?></h3>
                                            <p class="description">
                                                <?php printf(__('Add multiple banners to display in the %s location. Each banner can target specific devices.', 'banner-container-plugin'), strtolower($location_label)); ?>
                                            </p>
                                            
                                            <div id="iwz-<?php echo esc_attr($location_key); ?>-banners-container">
                                                <?php foreach ($location_banners as $index => $banner) : ?>
                                                    <div class="iwz-location-banner-item" data-index="<?php echo $index; ?>" data-location="<?php echo esc_attr($location_key); ?>">
                                                        <div class="iwz-location-banner-header">
                                                            <h4><?php printf(__('Banner %d', 'banner-container-plugin'), $index + 1); ?></h4>
                                                            <button type="button" class="button iwz-remove-location-banner" <?php echo count($location_banners) <= 1 ? 'style="display:none;"' : ''; ?>>
                                                                <?php _e('Remove', 'banner-container-plugin'); ?>
                                                            </button>
                                                        </div>
                                                        
                                                        <table class="form-table iwz-location-banner-settings">
                                                            <tr>
                                                                <th scope="row">
                                                                    <label for="iwz_<?php echo $location_key; ?>_banner_enabled_<?php echo $index; ?>">
                                                                        <?php _e('Enable Banner', 'banner-container-plugin'); ?>
                                                                    </label>
                                                                </th>
                                                                <td>
                                                                    <input type="checkbox" 
                                                                           id="iwz_<?php echo $location_key; ?>_banner_enabled_<?php echo $index; ?>" 
                                                                           name="iwz_banner_<?php echo $location_key; ?>_banners[<?php echo $index; ?>][enabled]" 
                                                                           value="1" 
                                                                           <?php checked(!empty($banner['enabled'])); ?> />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">
                                                                    <label for="iwz_<?php echo $location_key; ?>_banner_code_<?php echo $index; ?>">
                                                                        <?php _e('Banner Code', 'banner-container-plugin'); ?>
                                                                    </label>
                                                                </th>
                                                                <td>
                                                                    <textarea id="iwz_<?php echo $location_key; ?>_banner_code_<?php echo $index; ?>" 
                                                                              name="iwz_banner_<?php echo $location_key; ?>_banners[<?php echo $index; ?>][code]" 
                                                                              rows="6" 
                                                                              class="large-text code"><?php echo esc_textarea($banner['code'] ?? ''); ?></textarea>
                                                                    <p class="description">
                                                                        <?php _e('Enter the iframe or banner code to insert.', 'banner-container-plugin'); ?>
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">
                                                                    <label for="iwz_<?php echo $location_key; ?>_banner_device_<?php echo $index; ?>">
                                                                        <?php _e('Device Targeting', 'banner-container-plugin'); ?>
                                                                    </label>
                                                                </th>
                                                                <td>
                                                                    <select id="iwz_<?php echo $location_key; ?>_banner_device_<?php echo $index; ?>" 
                                                                            name="iwz_banner_<?php echo $location_key; ?>_banners[<?php echo $index; ?>][device_targeting]">
                                                                        <option value="all" <?php selected('all', $banner['device_targeting'] ?? 'all'); ?>>
                                                                            <?php _e('All Devices', 'banner-container-plugin'); ?>
                                                                        </option>
                                                                        <option value="desktop" <?php selected('desktop', $banner['device_targeting'] ?? 'all'); ?>>
                                                                            <?php _e('Desktop Only', 'banner-container-plugin'); ?>
                                                                        </option>
                                                                        <option value="mobile" <?php selected('mobile', $banner['device_targeting'] ?? 'all'); ?>>
                                                                            <?php _e('Mobile Only', 'banner-container-plugin'); ?>
                                                                        </option>
                                                                    </select>
                                                                    <p class="description">
                                                                        <?php _e('Choose which devices should display this banner.', 'banner-container-plugin'); ?>
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <p>
                                                <button type="button" class="iwz-add-location-banner button" data-location="<?php echo esc_attr($location_key); ?>">
                                                    <?php _e('Add Another Banner', 'banner-container-plugin'); ?>
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
                                            <label for="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled">
                                                <?php printf(__('Enable %s Banner', 'banner-container-plugin'), esc_html($location_label)); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="checkbox" id="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled" 
                                                   name="iwz_banner_<?php echo esc_attr($location_key); ?>_enabled" 
                                                   value="1" <?php checked(1, $enabled); ?> />
                                        </td>
                                    </tr>
                                    <tr class="iwz-banner-container-code-field">
                                        <th scope="row">
                                            <label for="iwz_banner_<?php echo esc_attr($location_key); ?>_code">
                                                <?php printf(__('%s Banner Code', 'banner-container-plugin'), esc_html($location_label)); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <textarea id="iwz_banner_<?php echo esc_attr($location_key); ?>_code" 
                                                      name="iwz_banner_<?php echo esc_attr($location_key); ?>_code" 
                                                      rows="6" class="large-text code"><?php echo esc_textarea($code); ?></textarea>
                                            <p class="description">
                                                <?php _e('Enter the iframe or banner code to insert at this location.', 'banner-container-plugin'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>

                <?php submit_button(__('Save Banner Settings', 'banner-container-plugin')); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Show/hide code field based on checkbox
            $('input[id$="_enabled"]').change(function() {
                var checkboxId = $(this).attr('id');
                var location = checkboxId.replace('iwz_banner_', '').replace('_enabled', '');
                var sectionContainer = $(this).closest('.iwz-banner-container-location-section');
                
                if ($(this).is(':checked')) {
                    // Show all code fields for this location
                    sectionContainer.find('.iwz-banner-container-code-field').removeClass('hidden').show();
                } else {
                    // Hide all code fields for this location
                    sectionContainer.find('.iwz-banner-container-code-field').addClass('hidden').hide();
                }
            });
            
            // Handle position changes for content banners
            $(document).on('change', '.iwz-banner-position-select', function() {
                var $paragraphField = $(this).closest('.iwz-content-banner-settings').find('.iwz-paragraph-field');
                if ($(this).val() === 'after_paragraph') {
                    $paragraphField.show();
                } else {
                    $paragraphField.hide();
                }
            });
            
            // Add new content banner
            $('#iwz-add-content-banner').click(function() {
                var $container = $('#iwz-content-banners-container');
                var newIndex = $container.find('.iwz-content-banner-item').length;
                var postTypesHtml = '';
                
                // Generate post types checkboxes
                <?php foreach ($this->get_post_types() as $post_type => $post_type_label) : ?>
                postTypesHtml += '<label><input type="checkbox" name="iwz_banner_the_content_banners[' + newIndex + '][post_types][]" value="<?php echo esc_attr($post_type); ?>" checked /> <?php echo esc_html($post_type_label); ?></label><br>';
                <?php endforeach; ?>
                
                var newBannerHtml = '<div class="iwz-content-banner-item" data-index="' + newIndex + '">' +
                    '<div class="iwz-content-banner-header">' +
                        '<h4><?php _e("Banner", "banner-container-plugin"); ?> ' + (newIndex + 1) + '</h4>' +
                        '<button type="button" class="button iwz-remove-banner"><?php _e("Remove", "banner-container-plugin"); ?></button>' +
                    '</div>' +
                    '<table class="form-table iwz-content-banner-settings">' +
                        '<tr>' +
                            '<th scope="row"><label for="iwz_content_banner_enabled_' + newIndex + '"><?php _e("Enable Banner", "banner-container-plugin"); ?></label></th>' +
                            '<td><input type="checkbox" id="iwz_content_banner_enabled_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][enabled]" value="1" /></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row"><label for="iwz_content_banner_code_' + newIndex + '"><?php _e("Banner Code", "banner-container-plugin"); ?></label></th>' +
                            '<td><textarea id="iwz_content_banner_code_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][code]" rows="6" class="large-text code"></textarea><p class="description"><?php _e("Enter the iframe or banner code to insert.", "banner-container-plugin"); ?></p></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row"><label for="iwz_content_banner_position_' + newIndex + '"><?php _e("Position", "banner-container-plugin"); ?></label></th>' +
                            '<td><select id="iwz_content_banner_position_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][position]" class="iwz-banner-position-select">' +
                                '<option value="top"><?php _e("Top of content", "banner-container-plugin"); ?></option>' +
                                '<option value="bottom"><?php _e("Bottom of content", "banner-container-plugin"); ?></option>' +
                                '<option value="after_paragraph"><?php _e("After specific paragraph", "banner-container-plugin"); ?></option>' +
                            '</select></td>' +
                        '</tr>' +
                        '<tr class="iwz-paragraph-field" style="display:none;">' +
                            '<th scope="row"><label for="iwz_content_banner_paragraph_' + newIndex + '"><?php _e("Paragraph Number", "banner-container-plugin"); ?></label></th>' +
                            '<td><input type="number" id="iwz_content_banner_paragraph_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][paragraph]" min="1" value="3" /><p class="description"><?php _e("Display after which paragraph? (Enter 1 for after first paragraph, 2 for second, etc.)", "banner-container-plugin"); ?></p></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row"><label for="iwz_content_banner_device_' + newIndex + '"><?php _e("Device Targeting", "banner-container-plugin"); ?></label></th>' +
                            '<td><select id="iwz_content_banner_device_' + newIndex + '" name="iwz_banner_the_content_banners[' + newIndex + '][device_targeting]">' +
                                '<option value="all"><?php _e("All Devices", "banner-container-plugin"); ?></option>' +
                                '<option value="desktop"><?php _e("Desktop Only", "banner-container-plugin"); ?></option>' +
                                '<option value="mobile"><?php _e("Mobile Only", "banner-container-plugin"); ?></option>' +
                            '</select><p class="description"><?php _e("Choose which devices should display this banner.", "banner-container-plugin"); ?></p></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row"><label><?php _e("Apply to Post Types", "banner-container-plugin"); ?></label></th>' +
                            '<td>' + postTypesHtml + '<p class="description"><?php _e("Select which post types should display this banner.", "banner-container-plugin"); ?></p></td>' +
                        '</tr>' +
                    '</table>' +
                '</div>';
                
                $container.append(newBannerHtml);
                updateRemoveButtons();
            });
            
            // Remove content banner
            $(document).on('click', '.iwz-remove-banner', function() {
                $(this).closest('.iwz-content-banner-item').remove();
                updateBannerIndices();
                updateRemoveButtons();
            });
            
            // Add location banner (head/footer)
            $(document).on('click', '.iwz-add-location-banner', function() {
                var location = $(this).data('location');
                var $container = $('#iwz-' + location + '-banners-container');
                var newIndex = $container.find('.iwz-location-banner-item').length;
                
                var newBannerHtml = '<div class="iwz-location-banner-item" data-index="' + newIndex + '" data-location="' + location + '">' +
                    '<div class="iwz-location-banner-header">' +
                        '<h4><?php _e("Banner", "banner-container-plugin"); ?> ' + (newIndex + 1) + '</h4>' +
                        '<button type="button" class="button iwz-remove-location-banner"><?php _e("Remove", "banner-container-plugin"); ?></button>' +
                    '</div>' +
                    '<table class="form-table iwz-location-banner-settings">' +
                        '<tr>' +
                            '<th scope="row"><label for="iwz_' + location + '_banner_enabled_' + newIndex + '"><?php _e("Enable Banner", "banner-container-plugin"); ?></label></th>' +
                            '<td><input type="checkbox" id="iwz_' + location + '_banner_enabled_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][enabled]" value="1" /></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row"><label for="iwz_' + location + '_banner_code_' + newIndex + '"><?php _e("Banner Code", "banner-container-plugin"); ?></label></th>' +
                            '<td><textarea id="iwz_' + location + '_banner_code_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][code]" rows="6" class="large-text code"></textarea><p class="description"><?php _e("Enter the iframe or banner code to insert.", "banner-container-plugin"); ?></p></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row"><label for="iwz_' + location + '_banner_device_' + newIndex + '"><?php _e("Device Targeting", "banner-container-plugin"); ?></label></th>' +
                            '<td><select id="iwz_' + location + '_banner_device_' + newIndex + '" name="iwz_banner_' + location + '_banners[' + newIndex + '][device_targeting]">' +
                                '<option value="all"><?php _e("All Devices", "banner-container-plugin"); ?></option>' +
                                '<option value="desktop"><?php _e("Desktop Only", "banner-container-plugin"); ?></option>' +
                                '<option value="mobile"><?php _e("Mobile Only", "banner-container-plugin"); ?></option>' +
                            '</select><p class="description"><?php _e("Choose which devices should display this banner.", "banner-container-plugin"); ?></p></td>' +
                        '</tr>' +
                    '</table>' +
                '</div>';
                
                $container.append(newBannerHtml);
                updateLocationRemoveButtons(location);
            });
            
            // Remove location banner
            $(document).on('click', '.iwz-remove-location-banner', function() {
                var location = $(this).closest('.iwz-location-banner-item').data('location');
                $(this).closest('.iwz-location-banner-item').remove();
                updateLocationBannerIndices(location);
                updateLocationRemoveButtons(location);
            });
            
            // Update banner indices after removal
            function updateBannerIndices() {
                $('#iwz-content-banners-container .iwz-content-banner-item').each(function(index) {
                    var $item = $(this);
                    $item.attr('data-index', index);
                    $item.find('h4').text('<?php _e("Banner", "banner-container-plugin"); ?> ' + (index + 1));
                    
                    // Update all form field names and IDs
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
            
            // Update location banner indices after removal
            function updateLocationBannerIndices(location) {
                $('#iwz-' + location + '-banners-container .iwz-location-banner-item').each(function(index) {
                    var $item = $(this);
                    $item.attr('data-index', index);
                    $item.find('h4').text('<?php _e("Banner", "banner-container-plugin"); ?> ' + (index + 1));
                    
                    // Update all form field names and IDs
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
            
            // Show/hide remove buttons
            function updateRemoveButtons() {
                var $items = $('#iwz-content-banners-container .iwz-content-banner-item');
                if ($items.length <= 1) {
                    $items.find('.iwz-remove-banner').hide();
                } else {
                    $items.find('.iwz-remove-banner').show();
                }
            }
            
            // Show/hide location remove buttons
            function updateLocationRemoveButtons(location) {
                var $items = $('#iwz-' + location + '-banners-container .iwz-location-banner-item');
                if ($items.length <= 1) {
                    $items.find('.iwz-remove-location-banner').hide();
                } else {
                    $items.find('.iwz-remove-location-banner').show();
                }
            }
            
            // Initialize visibility on page load
            $('input[id$="_enabled"]').each(function() {
                var sectionContainer = $(this).closest('.iwz-banner-container-location-section');
                
                if ($(this).is(':checked')) {
                    sectionContainer.find('.iwz-banner-container-code-field').removeClass('hidden').show();
                } else {
                    sectionContainer.find('.iwz-banner-container-code-field').addClass('hidden').hide();
                }
            });
            
            // Initialize remove buttons for content banners
            updateRemoveButtons();
            
            // Initialize remove buttons for location banners
            updateLocationRemoveButtons('wp_head');
            updateLocationRemoveButtons('wp_footer');
        });
        </script>
        <?php
    }
}
