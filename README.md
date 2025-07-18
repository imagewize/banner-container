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
- Blabber Footer Start (Top of Footer Area) - **Blabber theme exclusive feature** that displays banners just above the footer element with class `footer_wrap`. This location uses JavaScript insertion and is specifically designed for the Blabber theme footer structure. Each banner supports individual alignment options (left, center, right), background color customization, and margin/padding settings for precise spacing control.

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
5. For header, footer, and Blabber Footer Start banners, additional options include:
   - Choose alignment (left, center, or right) for each individual banner
   - Set wrapper background color for enhanced styling (for each individual banner):
     - **None (Transparent)**: No background color applied (default)
     - **Custom Color**: Choose a specific background color using the color picker
6. For footer banners, additional options include:
   - Enable sticky positioning to make banners stay at the bottom of the viewport
   - Set bottom spacing to prevent banners from covering other elements like age verification sliders
   - Set wrapper margin for outer spacing control (for each individual banner)
   - Set wrapper padding for inner spacing control (for each individual banner)
7. For Blabber Footer Start banners, additional spacing options include:
   - Set wrapper margin for outer spacing control (for each individual banner)
   - Set wrapper padding for inner spacing control (for each individual banner)
8. For content banners, additional options include:
   - Choose placement options (top, bottom, or after a specific paragraph)
   - Select which post types to display each banner on
9. Save your settings

## Age Verification Support

The plugin includes built-in support for age verification systems, allowing banners to be automatically hidden when users indicate they are below a certain age threshold. **Important: This feature requires a JavaScript-based age verification modal implementation.**

For more advanced technical details, refer to the [Development Guide](DEVELOPMENT.md).
