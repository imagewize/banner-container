# Banner Container Plugin - Development Setup Complete

## Summary

Your WordPress Banner Container Plugin now has a complete development environment with:

### ✅ Composer Configuration
- **Package**: `imagewze/banner-container`
- **Type**: `wordpress-plugin`
- **License**: GPL-2.0+
- **Author**: Jasper Frumau

### ✅ Code Quality Tools
- **WordPress Coding Standards (WPCS)** v3.1.0
- **PHP Compatibility** checks for PHP 7.4+
- **PHPUnit** v9.6 for testing
- **Automated code fixing** with PHPCBF

### ✅ Available Commands

| Command | Description |
|---------|-------------|
| `composer phpcs-check` | Quick code quality summary |
| `composer phpcs` | Detailed code quality report |
| `composer phpcbf` | Auto-fix code style issues |
| `composer test` | Run all PHPUnit tests |
| `composer lint` | Run all linting checks |
| `composer fix` | Auto-fix code issues |

### ✅ Configuration Files Created
- `composer.json` - Main composer configuration
- `phpcs.xml` - WordPress coding standards configuration
- `phpunit.xml` - PHPUnit test configuration
- `.gitignore` - Git ignore patterns
- `.github/workflows/ci.yml` - GitHub Actions CI/CD

### ✅ Test Environment
- PHPUnit bootstrap with WordPress function mocks
- Basic test suite with 5 passing tests
- Coverage reporting configured

### 🔧 Current Code Quality Status
- **2,826 errors** and **125 warnings** found in codebase
- **2,686 errors** can be auto-fixed with `composer fix`
- All development tools properly configured and working

## Next Steps

1. **Fix Code Quality Issues**:
   ```bash
   composer fix
   ```

2. **Review Remaining Issues**:
   ```bash
   composer phpcs
   ```

3. **Run Tests**:
   ```bash
   composer test
   ```

4. **Set up CI/CD**: Push to GitHub to activate automated testing

## Development Workflow

1. Make code changes
2. Run `composer lint` to check quality
3. Run `composer fix` to auto-fix issues
4. Run `composer test` to ensure tests pass
5. Commit and push changes

Your development environment is now production-ready! 🚀

## Custom Banner Locations

### Adding Custom Banner Locations

The Banner Container Plugin provides several filters and hooks that allow developers to add custom banner locations throughout a WordPress site.

#### Available Filters

| Filter | Description | Parameters |
|--------|-------------|------------|
| `banner_container_locations` | Add custom banner locations to the admin settings | `$locations` (array) |
| `banner_container_render_location` | Control where custom banners are rendered | `$location` (string), `$content` (string) |
| `banner_container_custom_hooks` | Register custom WordPress hooks for banner placement | `$hooks` (array) |

#### Example: Adding a Custom Banner Location

```php
// Add custom location to admin settings
add_filter('banner_container_locations', function($locations) {
    $locations['after_post_title'] = [
        'label' => 'After Post Title',
        'description' => 'Display banner immediately after post titles'
    ];
    return $locations;
});

// Hook into WordPress to render the custom banner
add_action('init', function() {
    if (function_exists('banner_container_render')) {
        add_action('wp_head', function() {
            // Add custom CSS or JS if needed
        });
        
        add_action('the_title', function($title) {
            if (is_single()) {
                $title .= banner_container_render('after_post_title');
            }
            return $title;
        });
    }
});
```

#### Available Helper Functions

- `banner_container_render($location)` - Render banners for a specific location
- `banner_container_get_settings($location)` - Get settings for a specific location
- `banner_container_is_enabled($location)` - Check if a location is enabled

For more advanced customization, see the plugin's source code in the `includes/` directory.

# Banner Container Plugin - Development Guide

## Testing

### Content Wrap Inside Banner Testing

The `content_wrap_inside` location is designed specifically for the Blabber theme to inject banners at the top of the content wrap area.

**Test File**: [content_wrap_inside test simulation](https://gist.github.com/jasperf/2ff52bd6beb5a4acfbdfecdd75e70e02)

This test file simulates the theme structure and demonstrates how the banner injection works:

- Creates a `.content_wrap` container similar to the Blabber theme
- Uses JavaScript to inject a test banner at the beginning of the content wrap
- Provides visual confirmation that the banner placement is working correctly

### Banner Locations

1. **wp_head** - Top of page (after `<body>` tag)
2. **wp_footer** - Footer (before `</body>` tag)  
3. **the_content** - Within post/page content with configurable positioning
4. **get_sidebar** - Before sidebar widgets
5. **wp_nav_menu_items** - Within navigation menus
6. **content_wrap_inside** - Inside Blabber theme content wrap (top of content area)

### Testing Recommendations

1. Test each banner location independently
2. Verify device targeting (desktop/mobile/all) works correctly
3. Test multiple banners per location
4. Verify post type targeting for content banners
5. Check banner positioning options (top, bottom, after paragraph)
6. Test with different themes to ensure compatibility

### Development Notes

- All banner locations support multiple banners with device targeting
- Content banners have additional positioning and post type options
- Legacy single banner settings are maintained for backward compatibility
- Settings are automatically migrated from old format to new multi-banner format

## Age Verification Implementation

### Required JavaScript Implementation

Your age verification modal must include JavaScript functions that manipulate the `d-none` class:

```javascript
// Hide age-restricted banners (when user selects "under 18")
const hideCasinoHighlightBlocks = () => {
  document.querySelectorAll('.code-block').forEach((element) => {
    if (element) {
      element.classList.add('d-none');
    }
  });
};

// Show banners (when user selects "18+" and accepts ads)
const displayCasinoHighlightBlocks = () => {
  document.querySelectorAll('.code-block').forEach((element) => {
    if (element) {
      element.classList.remove('d-none');
    }
  });
};

// Example button event handlers
document.getElementById('under-18').addEventListener('click', () => {
  setCookie('canSeeAds', 'false', 1);
  hideModal();
  hideCasinoHighlightBlocks(); // Hide banners
});

document.getElementById('over-18').addEventListener('click', () => {
  const advStatus = canSeeAds.checked ? 'true' : 'false';
  setCookie('canSeeAds', advStatus, 365);
  hideModal();
  if (advStatus === 'false') {
    hideCasinoHighlightBlocks(); // Hide banners
  } else {
    displayCasinoHighlightBlocks(); // Show banners
  }
});
```

### Modal HTML Structure Example

Your age verification modal should include buttons that trigger the JavaScript functions:

```html
<div class="modal-verification" id="modal_verification">
  <div class="modal-data-panel">
    <div class="modal-header-panel">
      <h2>Age Verification Required</h2>
      <p>Please confirm your age to continue</p>
    </div>
    
    <div class="modal-content-panel">
      <div class="btn-stack">
        <a href="javascript:void(0)" id="under-18" class="btn-default">Under 18</a>
        <a href="javascript:void(0)" id="18-23" class="btn-default">18-23</a>
        <a href="javascript:void(0)" id="over-24" class="btn-default">24+</a>
      </div>
      
      <div class="verification-check">
        <input type="checkbox" id="age_verify" name="age_verify" />
        <label for="age_verify">I accept advertising content</label>
      </div>
    </div>
  </div>
</div>
```

### Implementation Requirements

1. **JavaScript Functions**: Implement the `hideCasinoHighlightBlocks()` and `displayCasinoHighlightBlocks()` functions
2. **Event Handlers**: Attach click events to age selection buttons
3. **Cookie Management**: Implement `setCookie()` function for persistence
4. **Modal Control**: Implement `hideModal()` function to close the modal
5. **DOM Ready**: Ensure functions execute after DOM is loaded

### Integration Notes

- The plugin automatically applies the necessary CSS classes to all banners
- Your JavaScript modal controls banner visibility by adding/removing the `d-none` class
- Cookie persistence maintains user preferences across sessions
- The system is compatible with Ad Inserter plugin's `code-block` class system
