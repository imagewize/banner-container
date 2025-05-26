# Changelog

All notable changes to this project will be documented in this file.

## [1.8.1]
* Enhancement: Added device targeting support for Blabber Footer Start banner location
* Enhancement: Added multiple banner support for Blabber Footer Start location
* Admin Interface: Blabber Footer Start now includes "Add Another Banner" functionality
* Device Targeting: Individual banners can now target desktop, mobile, or all devices
* UI Improvement: Updated admin interface to match other multiple banner locations
* Code Quality: Ensured Blabber Footer Start follows same patterns as other multiple banner locations

## [1.8.0]
* New Feature: Added Blabber Footer Start banner location for displaying banners just above the footer element
* New Feature: Blabber Footer Start targets `footer.footer_wrap` element and uses JavaScript-based insertion
* Styling: Added specialized CSS for Blabber Footer Start banners with iframe height and width constraints
* Styling: Implemented responsive design with max-width 100% for mobile compatibility
* CSS Enhancement: Added high-specificity CSS rules to override Blabber theme's iframe styling conflicts
* Multiple Banner Support: Full support for multiple banners with individual device targeting (desktop/mobile/all)
* Admin Interface: Integrated into existing settings page with collapsible accordion interface
* Documentation: Updated README and welcome page with new banner location information
* Code Quality: Follows same patterns as existing Content Wrap banner location for consistency

## [1.7.0]
* Breaking Change: Updated sidebar banner location to use `dynamic_sidebar_before` hook instead of `get_sidebar`
* Improvement: Sidebar banners now display before any sidebar content (widgets) loads for better positioning
* UI Enhancement: Added explanatory note on settings page for sidebar banner behavior
* Code Quality: Maintained WordPress Coding Standards compliance
* Backward Compatibility: Legacy sidebar banner settings are automatically migrated to new hook system
* Documentation: Updated README to reflect new sidebar banner behavior

## [1.6.2]
* New Feature: Implemented accordion-style interface for banner location settings
* UI Improvement: All banner location sections now start in a collapsed state for better organization
* UI Improvement: Click on any banner location title bar to expand/collapse settings
* UI Improvement: Added visual status indicators showing enabled/disabled state and active banner count
* UI Improvement: Enhanced admin interface with smooth expand/collapse animations
* Bug Fix: Fixed accordion click handling to prevent conflicts with checkbox interactions
* Styling: Added comprehensive CSS for accordion interface with proper visual hierarchy

## [1.6.1]
* Bug Fix: Fixed welcome page redirect issue by removing unnecessary page parameter check that was preventing activation redirect
* Improvement: Streamlined activation redirect logic to ensure users are properly directed to welcome page after plugin activation
* Code Quality: Simplified redirect conditions while maintaining bulk activation protection

## [1.6.0]
* New Feature: Added Content Wrap banner location for displaying banners inside content wrapper elements
* New Feature: Content Wrap banner supports JavaScript-based insertion with DOM-ready event handling
* Styling: Added specialized CSS for Content Wrap banners with iframe height and width constraints
* Styling: Implemented responsive design with max-width 100% for mobile compatibility
* CSS Override: Added high-specificity CSS rules to handle problematic theme iframe styling conflicts
* Enhanced Targeting: Content Wrap banners include proper CSS class targeting for blabber_resize and trx_addons_resize elements
* Code Quality: Added sanitization and proper HTML handling for Content Wrap banner insertion

## [1.5.5]
* Bug Fix: Fixed HTML escaping issue that prevented iframe banners from rendering correctly
* Bug Fix: Resolved double banner display issue where banners appeared twice on themes supporting wp_body_open
* Improvement: Enhanced banner HTML sanitization with custom method allowing iframes and banner-related HTML tags
* Improvement: Implemented flag-based prevention system to ensure banners display only once
* Code Quality: Replaced esc_js() with wp_json_encode() for proper HTML content handling in JavaScript
* Theme Compatibility: Better fallback system for themes with and without wp_body_open support

## [1.5.4]
* Bug Fix: Fixed translation loading timing issue by moving plugin initialization from `plugins_loaded` to `init` action
* Internationalization: Ensures textdomain is loaded at priority 10 before plugin initializes at priority 20
* Code Quality: Properly resolved WordPress 6.7+ translation loading notices by ensuring correct action hook order

## [1.5.3]
* Bug Fix: Fixed translation loading issue where textdomain was being loaded too early
* Internationalization: Added proper textdomain loading on the 'init' action to comply with WordPress 6.7+ requirements
* Code Quality: Resolved PHP notice about incorrect translation loading timing

## [1.5.2]
* Documentation: Enhanced README with proper links to development documentation
* Development Guide: Added comprehensive custom banner locations documentation
* Developer Experience: Added technical details for filters, hooks, and helper functions for custom banner implementations

## [1.5.1]
* Changelog in its own file

## [1.5.0]
* PHPCS
* Github Actions
* Files WordPress CS Standards Update

## [1.4.2]
* README clarifications.

## [1.4.1]
* Settings Notification: Added success notification banner when settings are saved
* Improved User Experience: Smooth scroll to top after saving to show confirmation message
* Auto-dismiss notification after 5 seconds with fade-out animation
* Enhanced admin interface with better visual feedback for settings updates

## [1.4.0]
* Device Targeting: Add mobile/desktop targeting options for all banner locations (header, footer, content, sidebar, navigation menu)
* Multiple Banners for All Locations: Support for multiple banners in all locations with individual device targeting
* Enhanced Banner Management: All banner locations now support adding, removing, and managing multiple banners
* Improved Admin Interface: Updated settings page to handle multiple banners with device targeting across all locations
* Backward Compatibility: Seamless migration from single banner configurations to new multiple banner system

## [1.3.0]
* Multiple Banners in Content: Add multiple banners to the "Within Content" location
* Each content banner can have individual positioning, post type restrictions, and enable/disable settings
* Backward compatibility with existing single banner configurations
* Improved admin interface for managing multiple content banners

## [1.2.3]
* Header Placement Clarification on Options Page

## [1.2.2]
* settings page text area visibility improvement

## [1.2.1]
* prefix iwz
* additional renaming

## [1.2.0]
* proper namespacing
* plugin renaming

## [1.0.1]
* Removed dependency on Advanced Custom Fields (ACF) plugin
* Added native WordPress settings API integration
* Improved plugin architecture for better separation of concerns
* Enhanced code organization for better maintainability
* Optimized admin interface with better settings controls
* Fixed potential issues in uninstall process

## [1.0.0]
* Initial release
