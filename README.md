# Banner Iframe Plugin

A WordPress plugin to add banner iframes to different locations in your WordPress theme.

## Description

The Banner Iframe Plugin allows you to easily add iframe codes to various locations in your WordPress site, such as:

- Header (before `</head>`)
- Footer (before `</body>`)
- Within Content (with options for placement)
- Before Sidebar
- In Navigation Menu

## Requirements

- WordPress 5.0 or higher
- PHP 5.6 or higher

## Development

This plugin uses Composer for PHP dependency management and development tools.

### Setting up the development environment

1. Clone the repository
2. Run `composer install` to install dependencies
3. Run `composer phpcs` to check code against WordPress coding standards
4. Run `composer phpcbf` to automatically fix code standard violations where possible
5. Run `composer test` to run unit tests (requires WordPress test environment)

## Installation

1. Upload the `banner-iframe-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the 'Banner Iframes' menu item in the admin dashboard to configure your settings

## Usage

1. Navigate to the Banner Iframes settings page in your WordPress admin
2. Enable the locations where you want to display banners
3. Enter your iframe or banner HTML code for each location
4. For content banners, choose placement options (top, bottom, or after a specific paragraph)
5. Save your settings

## Frequently Asked Questions

### Can I add banners to custom locations?

Yes! The plugin provides filters that allow developers to add custom banner locations.

### What type of code can I add?

You can add any HTML code, including iframes, JavaScript snippets, or plain HTML.

### Will this plugin slow down my site?

The plugin is lightweight and only loads the necessary code on the frontend. It should not cause any noticeable performance impact.

## Changelog

### 1.1.0
* Implemented PHPUnit tests for improved code reliability
* Added continuous integration with GitHub Actions
* Updated code to follow WordPress Coding Standards

### 1.0.1
* Removed dependency on Advanced Custom Fields (ACF) plugin
* Added native WordPress settings API integration
* Improved plugin architecture for better separation of concerns
* Enhanced code organization for better maintainability
* Optimized admin interface with better settings controls
* Fixed potential issues in uninstall process

### 1.0.0
* Initial release
