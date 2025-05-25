# Banner Container Plugin

A WordPress plugin to add banners to different locations in your WordPress theme.

## Description

The Banner Container Plugin allows you to easily add iframe codes to various locations in your WordPress site, such as:

- Header (after initial `<body>`tag) - with support for multiple banners and device targeting
- Footer (before `</body>` tag) - with support for multiple banners and device targeting
- Content (beginning content, end content or after x paragraphs) (with options for multiple banners, individual placement, and device targeting)
- Before Sidebar - with support for multiple banners and device targeting
- In Navigation Menu - with support for multiple banners and device targeting

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
5. For content banners, additional options include:
   - Choose placement options (top, bottom, or after a specific paragraph)
   - Select which post types to display each banner on
6. Save your settings

## Frequently Asked Questions

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
