=== Banner Iframe Plugin ===
Contributors: yourwordpressusername
Donate link: https://yourwebsite.com/donate
Tags: iframe, banner, header, footer, content
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.1.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to add banner iframes to different locations in your WordPress theme.

== Description ==

The Banner Iframe Plugin allows you to easily add iframe codes to various locations in your WordPress site, such as:

- Header (before `</head>`)
- Footer (before `</body>`)
- Within Content (with options for placement)
- Before Sidebar
- In Navigation Menu

**Features Include:**

* Easy-to-use settings interface
* Multiple banner locations
* Content placement options
* Custom code support for any HTML/JavaScript
* Developer hooks and filters

== Installation ==

1. Upload the `banner-iframe-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the 'Banner Iframes' menu item in the admin dashboard to configure your settings

== Usage ==

1. Navigate to the Banner Iframes settings page in your WordPress admin
2. Enable the locations where you want to display banners
3. Enter your iframe or banner HTML code for each location
4. For content banners, choose placement options (top, bottom, or after a specific paragraph)
5. Save your settings

== Frequently Asked Questions ==

= Can I add banners to custom locations? =

Yes! The plugin provides filters that allow developers to add custom banner locations.

= What type of code can I add? =

You can add any HTML code, including iframes, JavaScript snippets, or plain HTML.

= Will this plugin slow down my site? =

The plugin is lightweight and only loads the necessary code on the frontend. It should not cause any noticeable performance impact.

== Screenshots ==

1. Plugin settings page
2. Banner placement options
3. Example of a banner in content

== Changelog ==

= 1.1.0 =
* Implemented PHPUnit tests for improved code reliability
* Added continuous integration with GitHub Actions
* Updated code to follow WordPress Coding Standards

= 1.0.1 =
* Removed dependency on Advanced Custom Fields (ACF) plugin
* Added native WordPress settings API integration
* Improved plugin architecture for better separation of concerns
* Enhanced code organization for better maintainability
* Optimized admin interface with better settings controls
* Fixed potential issues in uninstall process

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
This version removes the ACF dependency, uses native WordPress settings API, adds tests and follows WordPress Coding Standards. No data migration needed.
