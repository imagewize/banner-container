# Banner Container Plugin

A WordPress plugin to add banners to different locations in your WordPress theme.

## Description

The Banner Container Plugin allows you to easily add iframe codes to various locations in your WordPress site, such as:

- Header (after initial `<body>`tag) - with support for multiple banners, device targeting, and alignment options (left, center, right)
- Footer (before `</body>` tag) - with support for multiple banners, device targeting, and alignment options (left, center, right)
- Content (beginning content, end content or after x paragraphs) (with options for multiple banners, individual placement, and device targeting)
- Before Sidebar Content - Uses `dynamic_sidebar_before` hook to display banners before any sidebar widgets load, ensuring proper positioning above all sidebar content. Supports multiple banners and device targeting.
- In Navigation Menu - with support for multiple banners and device targeting
- Content Wrap (inside content wrapper elements) - **Blabber theme exclusive feature** that targets elements with the `content_wrapper` CSS class. This location uses JavaScript insertion and is specifically designed for the Blabber theme structure.
- Blabber Footer Start (Top of Footer Area) - **Blabber theme exclusive feature** that displays banners just above the footer element with class `footer_wrap`. This location uses JavaScript insertion and is specifically designed for the Blabber theme footer structure.

## Requirements

- WordPress 5.0 or higher

## Installation

1. Upload the `banner-container-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the 'Banner Container' menu item in the admin dashboard to configure your settings

## Usage

1. Navigate to the Banner Container settings page in your WordPress admin
2. Enable the locations where you want to display banners
3. Enter your iframe or banner HTML code for each location
4. For all banner locations, you can add multiple banners with individual settings:
   - Choose device targeting (all devices, desktop only, or mobile only)
   - Enable or disable individual banners
5. For header and footer banners, additional options include:
   - Choose alignment (left, center, or right) for each individual banner
6. For content banners, additional options include:
   - Choose placement options (top, bottom, or after a specific paragraph)
   - Select which post types to display each banner on
7. Save your settings

## Banner Wrapper Classes

### Overview

The Banner Container Plugin supports wrapper CSS classes for all banner types, allowing you to apply custom styling and better control banner appearance. The plugin now includes a comprehensive default wrapper class system with unique styling for each banner location.

### How Wrapper Classes Work

When you specify a wrapper class for a banner:
- The banner HTML/iframe code gets wrapped in a `<div>` element
- Default CSS classes are automatically applied based on the banner location
- Additional custom CSS class(es) can be added alongside the defaults (additive system)
- This allows for enhanced styling control and better integration with your theme

### Setting Wrapper Classes

1. **For Content Banners**: In the banner settings, look for the "Wrapper CSS Class" field
2. **For Location Banners**: Each banner location includes a "Wrapper CSS Class" option
3. **Multiple Classes**: You can specify multiple CSS classes separated by spaces (e.g., `custom-banner highlight-banner`)
4. **Additive System**: Your custom classes are added to the default classes, not replacing them

### Default Wrapper Classes

Each banner location now has its own unique default wrapper class with predefined styling:

- **Header (Top of Page) (wp_head)**: `iwz-head-banner`
- **Footer (wp_footer)**: `iwz-footer-banner`
- **Within Content (the_content)**: `iwz-content-banner`
- **Before Sidebar Content**: `iwz-sidebar-banner`
- **In Navigation Menu**: `iwz-menu-banner`
- **Blabber Content Wrap**: `iwz-blabber-header-banner`
- **Blabber Footer Start**: `iwz-blabber-footer-banner`

### Additive Class System

The wrapper class system is additive, meaning:
- Default classes are always applied based on location
- Your custom classes are added alongside the defaults
- Example: If you add `custom-banner` to a header banner, the final classes will be `iwz-blabber-header-banner custom-banner`

### Example Usage

```html
<!-- Default wrapper (automatic) -->
<div class="iwz-head-banner">
    <iframe src="..."></iframe>
</div>

<!-- With additional custom class -->
<div class="iwz-head-banner custom-banner">
    <iframe src="..."></iframe>
</div>
```

### Styling Wrapper Classes

The plugin includes comprehensive default styling for all wrapper classes. You can also add custom CSS to your theme to enhance or override the default styles:

```css
/* Customize default header banner styling */
.iwz-blabber-header-banner {
    margin: 20px 0;
    text-align: center;
    border: 1px solid #ddd;
}

/* Add custom styling for footer banners */
.iwz-footer-banner {
    padding: 15px;
    background: #f9f9f9;
    border-radius: 5px;
}

/* Style content banners */
.iwz-content-banner {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 30px 0;
}

/* Custom class for specific banners */
.custom-banner {
    border: 2px solid #007cba;
    padding: 10px;
}
```

## Age Verification Support

### Overview

The Banner Container Plugin includes built-in support for age verification systems, allowing banners to be automatically hidden when users indicate they are below a certain age threshold. **Important: This feature requires a JavaScript-based age verification modal implementation.**

### How Age Verification Works

The plugin provides CSS classes that work with JavaScript modal systems:

- **`.d-none`** - Primary utility class for hiding elements with `display: none !important`
- **`.code-block.d-none`** - Specific targeting for Ad Inserter plugin integration
- **`.iwz-blabber-footer-banner.d-none`** - Targeted hiding for Blabber footer banners

### JavaScript Modal Requirement

**This feature only works if you implement a JavaScript modal that adds/removes the `d-none` class.** The plugin provides the CSS; you must provide the JavaScript modal functionality.

### Implementation Details

For complete implementation examples including JavaScript functions, modal HTML structure, and event handlers, see the [Age Verification Development Guide](DEVELOPMENT.md#age-verification-implementation) in the development documentation.

### Complete Implementation Example

For a complete implementation example, see:
- [Age Verification JavaScript](https://github.com/imagewize/wordpress-tools/blob/main/age-verification/age-verification.js)
- [Footer with Modal](https://github.com/imagewize/wordpress-tools/blob/main/age-verification/footer.php)

### Compatibility

The plugin is compatible with:

- **Ad Inserter Plugin** - Works with the `code-block` class system
- **Custom Age Verification Modals** - Any JavaScript system that adds/removes the `d-none` class
- **Blabber Theme Age Verification** - Integrated with the Blabber theme's built-in age verification modal

### Setting Up Age Verification

To use age verification with your banners:

1. **Install the Banner Container Plugin**: Configure your banners normally through the plugin settings
2. **Implement JavaScript Modal**: Create an age verification modal with the required JavaScript functions (see [Development Guide](DEVELOPMENT.md#age-verification-implementation))
3. **Add Modal HTML**: Include the modal HTML structure in your theme's footer.php
4. **Test Functionality**: Verify that banners hide/show based on age selection and cookie persistence

### Technical Notes

- The `.d-none` class uses `!important` declarations to ensure reliable hiding
- Cookie-based persistence maintains user preferences across sessions
- The system targets elements with the `code-block` class (commonly used by Ad Inserter)
- All Banner Container plugin banners automatically include the necessary CSS classes

## Frequently Asked Questions

### How do banner alignment options work for header and footer locations?

**Banner Alignment for Header and Footer**

Header (wp_head) and footer (wp_footer) banner locations now support individual alignment settings for each banner:

**Available Alignment Options:**
- **Left**: Banner aligns to the left side of its container (default)
- **Center**: Banner centers within its container
- **Right**: Banner aligns to the right side of its container

**How Alignment Works:**
- Each banner in header and footer locations can have its own alignment setting
- Alignment is applied via CSS `text-align` property on the banner wrapper
- The alignment affects the banner content within its wrapper div
- Default alignment is "left" to maintain backward compatibility with existing banners

**Setting Alignment:**
1. Go to Banner Container settings
2. Expand the "Top of Page (After <body>)" or "Footer (Before </body>)" section
3. For each banner, select the desired alignment from the "Banner Alignment" dropdown
4. Choose from Left, Center, or Right alignment options
5. Save settings

**Technical Implementation:**
- Alignment is applied to the wrapper div class (`iwz-head-banner` or `iwz-footer-banner`)
- The CSS `text-align` property controls the banner positioning
- Each banner can have independent alignment regardless of other banners in the same location

### How do wrapper classes work in the Banner Container Plugin?

The Banner Container Plugin includes a comprehensive wrapper class system that automatically applies default CSS classes to all banners based on their location. This system is additive, meaning you can add your own custom classes alongside the defaults.

**Default Classes by Location:**
- Top of Page banners: `iwz-head-banner`
- Footer banners: `iwz-footer-banner`
- Content banners: `iwz-content-banner`
- Sidebar banners: `iwz-sidebar-banner`
- Menu banners: `iwz-menu-banner`
- Blabber Content Wrap banners: `iwz-blabber-header-banner`
- Blabber Footer banners: `iwz-blabber-footer-banner`

**How it works:**
- Default classes are always applied automatically
- Any custom classes you specify are added alongside the defaults
- Example: Adding `custom-style` to a header banner results in `iwz-blabber-header-banner custom-style`

This ensures consistent styling while allowing customization for specific needs.

### What are the Blabber theme exclusive banner locations and when should I use them?

The Banner Container Plugin includes two specialized banner placements designed exclusively for the **Blabber theme**:

#### Content Wrap Banner Location
This feature targets elements with the `content_wrapper` CSS class that is specific to the Blabber theme structure.

**When to use:**
- You are using the Blabber theme
- You want banners to appear inside the main content wrapper area
- You need precise control over banner sizing within the Blabber theme's content structure

#### Blabber Footer Start Banner Location
This feature targets the footer element with class `footer_wrap` and displays banners just above the footer element.

**When to use:**
- You are using the Blabber theme
- You want banners to appear just above the footer section
- You need banner placement specifically positioned before the Blabber theme's footer structure

**Important Notes for both locations:**
- **Blabber theme only**: These features only work with the Blabber theme as they specifically target Blabber theme CSS classes
- Use JavaScript-based insertion with DOM-ready event handling for proper placement
- Include specialized CSS styling with iframe constraints (100px height, 640px width)
- Responsive design with max-width 100% for mobile compatibility
- High-specificity CSS rules to override Blabber theme's iframe styling (`blabber_resize`, `trx_addons_resize` classes)

**When NOT to use:**
- You are using any theme other than Blabber
- Your theme doesn't have elements with the required CSS classes (`content_wrapper` or `footer_wrap`)
- In these cases, use other banner locations like Header, Footer, or Content instead

### Can I add banners to custom locations?

Yes! The plugin provides filters that allow developers to add custom banner locations. See the [Development Documentation](DEVELOPMENT.md#custom-banner-locations) for technical details on available hooks and filters.

### What type of code can I add?

You can add any HTML code, including iframes, JavaScript snippets, or plain HTML.

### Will this plugin slow down my site?

The plugin is lightweight and only loads the necessary code on the frontend. It should not cause any noticeable performance impact.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed list of changes and version history.

## Development

### Prerequisites

- PHP 7.4 or higher
- Composer
- WordPress 5.0 or higher

### Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```

### Code Quality

This project uses WordPress Coding Standards (WPCS) for code quality checks.

#### Available Commands

- **Check code quality**: `composer phpcs-check`
- **Check and show detailed issues**: `composer phpcs`
- **Automatically fix code style issues**: `composer fix`
- **Run all linting checks**: `composer lint`
- **Run tests**: `composer test`

#### Manual Commands

You can also run the tools directly:

```bash
# Check coding standards
./vendor/bin/phpcs -d memory_limit=512M --standard=WordPress --ignore=vendor,tests .

# Fix coding standards automatically
./vendor/bin/phpcbf -d memory_limit=512M --standard=WordPress --ignore=vendor,tests .

# Run tests
./vendor/bin/phpunit
```

### Contributing

1. Follow WordPress Coding Standards
2. Run `composer lint` before submitting
3. Ensure all tests pass with `composer test`
4. Write tests for new functionality
