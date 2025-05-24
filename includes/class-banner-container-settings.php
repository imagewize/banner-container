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
            'wp_head' => __('Header (Before </head>)', 'banner-container-plugin'),
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

            // For content banner, add additional settings
            if ($location_key === 'the_content') {
                // Position setting
                register_setting(
                    'iwz_banner_container_settings',
                    'iwz_banner_content_position',
                    array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                        'default' => 'top',
                    )
                );

                // Paragraph number setting
                register_setting(
                    'iwz_banner_container_settings',
                    'iwz_banner_content_paragraph',
                    array(
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                        'default' => 3,
                    )
                );

                // Post types setting
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

                            <?php if ($location_key === 'the_content') : ?>
                                <?php 
                                    $position = get_option('iwz_banner_content_position', 'top');
                                    $paragraph = get_option('iwz_banner_content_paragraph', 3);
                                    $post_types = get_option('iwz_banner_content_post_types', array('post'));
                                ?>
                                <tr class="iwz-banner-container-code-field">
                                    <th scope="row">
                                        <label for="iwz_banner_content_position">
                                            <?php _e('Content Banner Position', 'banner-container-plugin'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="iwz_banner_content_position" name="iwz_banner_content_position">
                                            <option value="top" <?php selected('top', $position); ?>>
                                                <?php _e('Top of content', 'banner-container-plugin'); ?>
                                            </option>
                                            <option value="bottom" <?php selected('bottom', $position); ?>>
                                                <?php _e('Bottom of content', 'banner-container-plugin'); ?>
                                            </option>
                                            <option value="after_paragraph" <?php selected('after_paragraph', $position); ?>>
                                                <?php _e('After specific paragraph', 'banner-container-plugin'); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="iwz-banner-container-paragraph-field">
                                    <th scope="row">
                                        <label for="iwz_banner_content_paragraph">
                                            <?php _e('Paragraph Number', 'banner-container-plugin'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="number" id="iwz_banner_content_paragraph" 
                                               name="iwz_banner_content_paragraph" min="1" 
                                               value="<?php echo esc_attr($paragraph); ?>" />
                                        <p class="description">
                                            <?php _e('Display after which paragraph? (Enter 1 for after first paragraph, 2 for second, etc.)', 'banner-container-plugin'); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr class="iwz-banner-container-code-field">
                                    <th scope="row">
                                        <label for="iwz_banner_content_post_types">
                                            <?php _e('Apply to Post Types', 'banner-container-plugin'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <?php foreach ($this->get_post_types() as $post_type => $post_type_label) : ?>
                                            <label>
                                                <input type="checkbox" 
                                                       name="iwz_banner_content_post_types[]" 
                                                       value="<?php echo esc_attr($post_type); ?>" 
                                                       <?php checked(in_array($post_type, (array) $post_types)); ?> />
                                                <?php echo esc_html($post_type_label); ?>
                                            </label><br>
                                        <?php endforeach; ?>
                                        <p class="description">
                                            <?php _e('Select which post types should display this content banner.', 'banner-container-plugin'); ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
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
                    
                    // Special handling for content banner
                    if (location === 'the_content') {
                        var position = $('#iwz_banner_content_position').val();
                        if (position === 'after_paragraph') {
                            $('.iwz-banner-container-paragraph-field').removeClass('hidden').show();
                        }
                    }
                } else {
                    // Hide all code fields for this location
                    sectionContainer.find('.iwz-banner-container-code-field').addClass('hidden').hide();
                    
                    // Special handling for content banner
                    if (location === 'the_content') {
                        $('.iwz-banner-container-paragraph-field').addClass('hidden').hide();
                    }
                }
            });
            
            // Show/hide paragraph field based on position selection
            $('#iwz_banner_content_position').change(function() {
                var contentEnabled = $('#iwz_banner_the_content_enabled').is(':checked');
                if ($(this).val() === 'after_paragraph' && contentEnabled) {
                    $('.iwz-banner-container-paragraph-field').removeClass('hidden').show();
                } else {
                    $('.iwz-banner-container-paragraph-field').addClass('hidden').hide();
                }
            });
            
            // Initialize visibility on page load
            $('input[id$="_enabled"]').each(function() {
                var checkboxId = $(this).attr('id');
                var location = checkboxId.replace('iwz_banner_', '').replace('_enabled', '');
                var sectionContainer = $(this).closest('.iwz-banner-container-location-section');
                
                if ($(this).is(':checked')) {
                    sectionContainer.find('.iwz-banner-container-code-field').removeClass('hidden').show();
                } else {
                    sectionContainer.find('.iwz-banner-container-code-field').addClass('hidden').hide();
                }
            });
            
            // Initialize content banner specific fields
            var contentEnabled = $('#iwz_banner_the_content_enabled').is(':checked');
            var position = $('#iwz_banner_content_position').val();
            
            if (contentEnabled && position === 'after_paragraph') {
                $('.iwz-banner-container-paragraph-field').removeClass('hidden').show();
            } else {
                $('.iwz-banner-container-paragraph-field').addClass('hidden').hide();
            }
        });
        </script>
        <?php
    }
}
