# Banner Container Plugin

A WordPress plugin to add banners to different locations in your WordPress theme.

## Description

The Banner Container Plugin allows you to easily add iframe codes to various locations in your WordPress site, such as:

- Header (before `</head>`)
- Footer (before `</body>`)
- Within Content (with options for placement)
- Before Sidebar
- In Navigation Menu

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

### 1.2.3
* Header Placement Clarification on Options Page
### 1.2.2
* settings page text area visibility improvement
### 1.2.1
* prefix iwz
* additional renaming

### 1.2.0
* proper namespacing
* plugin renaming

### 1.0.1
* Removed dependency on Advanced Custom Fields (ACF) plugin
* Added native WordPress settings API integration
* Improved plugin architecture for better separation of concerns
* Enhanced code organization for better maintainability
* Optimized admin interface with better settings controls
* Fixed potential issues in uninstall process

### 1.0.0
* Initial release
