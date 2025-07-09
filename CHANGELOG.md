# Changelog

All notable changes to this project will be documented in this file.

## [1.9.14]
* Enhancement: Added unique ID system for Blabber Footer Start banners to prevent alignment conflicts between multiple banners.
* Enhancement: Each Blabber Footer Start banner now gets a unique ID (iwz-banner-blabber_footer_start-0, iwz-banner-blabber_footer_start-1, etc.).
* Enhancement: Enhanced CSS specificity with unique banner ID targeting to ensure individual banner alignment settings work independently.
* Technical Enhancement: Updated banner rendering logic to include banner index tracking for Blabber Footer Start banners.
* Technical Enhancement: Improved CSS selectors with div[id^="iwz-banner-blabber_footer_start-"] targeting for better specificity.
* Bug Fix: Resolved alignment conflicts when multiple Blabber Footer Start banners have different alignment settings.
* Code Quality: Enhanced banner wrapper HTML generation to support unique IDs for both styled and non-styled banner wrappers.
* User Experience: Multiple Blabber Footer Start banners now maintain individual alignment settings without interference.

## [1.9.13]
* Bug Fix: Fixed desktop alignment for Blabber Footer Start banners being overridden by mobile centering styles.
* Enhancement: Improved CSS specificity to ensure desktop alignment settings (left, center, right) are respected.
* Enhancement: Reorganized CSS media queries to properly separate desktop and mobile banner alignment behavior.
* Technical Enhancement: Added desktop-specific media query (@media screen and (min-width: 769px)) for proper alignment control.
* Technical Enhancement: Implemented explicit alignment classes (.align-left, .align-center, .align-right) for desktop banners.
* Technical Enhancement: Enhanced mobile centering to only apply on screens 768px and below without affecting desktop alignment.
* Code Quality: Restructured Blabber Footer Start banner CSS to use proper media query hierarchy.
* Code Quality: Updated base styles to remove conflicting centering that was overriding desktop alignment.
* User Experience: Desktop alignment now uses flexbox justify-content for precise control while maintaining mobile centering.
* CSS Enhancement: Improved CSS organization with clear separation between desktop and mobile behavior.

## [1.9.12]
* Bug Fix: Fixed centering issue for Blabber Footer Start banners on mobile devices.
* Enhancement: Updated CSS to use `margin: 0 auto` for proper iframe centering within banner containers.
* Enhancement: Added mobile-specific CSS rules using flexbox for improved centering on smaller screens.
* Enhancement: Updated JavaScript to use `margin: 0 auto` specifically for blabber footer banner iframes to maintain centering.
* Technical Enhancement: Added comprehensive mobile responsive styles for 250x250 banner dimensions.
* Code Quality: Fixed PHPCS whitespace error in CSS file.
* User Experience: Blabber Footer Start banners now properly center on all device sizes, particularly mobile.
* CSS Enhancement: Implemented dual centering approach using both flexbox and auto margins for maximum compatibility.

## [1.9.11]
* New Feature: Added "None (Transparent)" option for wrapper background colors on header, footer, and Blabber Footer Start banners.
* Enhancement: Background color settings now display as radio buttons with "None" and "Custom Color" options instead of simple color picker.
* User Experience: Default background color is now set to "None" (transparent) for all banner types, providing cleaner integration with themes.
* Admin Interface: Background color fields now show clear radio button options with color picker that only appears when "Custom Color" is selected.
* Technical Enhancement: Updated sanitization functions to handle new background color structure with proper fallback for legacy values.
* Code Quality: Enhanced PHPCS compliance with proper input validation and sanitization for background color fields.
* CSS Enhancement: Removed hardcoded default background colors from CSS, allowing for true transparent backgrounds by default.
* Backward Compatibility: Existing banners with set background colors continue to work without changes.
* JavaScript Enhancement: Added radio button handling for background color selection in dynamically created banners.

## [1.9.10]
* New Feature: Added wrapper margin and padding controls for footer banners (wp_footer).
* Enhancement: Footer banners now have the same wrapper styling options as Blabber Footer Start banners.
* Enhancement: Per-banner control of wrapper margin and padding for footer banners provides more granular spacing control.
* Admin Interface: Added margin and padding fields to footer banner settings in admin interface.
* JavaScript Enhancement: Dynamically created footer banners now include margin and padding fields.
* Code Quality: Updated sanitization functions to handle wrapper margin and padding for footer banners with PHPCS compliance.
* User Experience: Footer banners can now be individually styled with custom margin and padding without affecting other banners.
* Consistency: Unified styling options between footer and Blabber Footer Start banner types.

## [1.9.9]
* Enhancement: Improved footer banner bottom spacing functionality with proper per-banner control.
* New Feature: Added bottom spacing support to JavaScript for dynamically created footer banners.
* Bug Fix: Fixed missing bottom spacing field when adding new footer banners via admin interface.
* Technical Enhancement: Updated wrap_banner_html function to properly handle bottom spacing parameter for footer banners.
* Code Quality: Enhanced PHPCS compliance throughout the codebase with proper parameter handling.
* User Experience: Bottom spacing now properly prevents footer banners from covering age verification sliders and other bottom elements.
* Admin Interface: Bottom spacing field is now properly included when adding new footer banners dynamically.

## [1.9.8]
* Breaking Change: Moved alignment, background color, margin, and padding settings from global to per-banner level for `blabber_footer_start` banners.
* Enhancement: Each Blabber Footer Start banner now has individual control over alignment (left, center, right), background color, margin, and padding settings.
* User Experience: Allows different styling for mobile and desktop Blabber Footer Start banners through device targeting and individual banner settings.
* Admin Interface: Removed global alignment, margin, and padding fields for Blabber Footer Start location, added per-banner controls instead.
* Backward Compatibility: Legacy global settings are still supported for existing single banner configurations.
* Code Quality: Updated sanitization functions to handle per-banner styling settings with proper PHPCS compliance.
* Technical Enhancement: Simplified settings structure by removing redundant global styling options for Blabber Footer Start banners.

## [1.9.7]
* New Feature: Added alignment options (left, center, right) for Blabber Footer Start banners.
* New Feature: Added wrapper margin setting for Blabber Footer Start banners for custom spacing control.
* New Feature: Added wrapper padding setting for Blabber Footer Start banners for inner spacing control.
* New Feature: Added wrapper background color customization for Blabber Footer Start banners.
* Enhancement: Blabber Footer Start banners now support the same alignment and styling options as header and footer banners.
* Enhancement: Individual Blabber Footer Start banners can override global alignment settings.
* Enhancement: Blabber Footer Start banners default to transparent background for cleaner integration.
* Admin Interface: Added alignment dropdown, margin field, padding field, and background color picker for Blabber Footer Start location.
* CSS Enhancement: Extended wrapper styling system to include Blabber Footer Start banners with margin and padding support.
* User Experience: Improved control over Blabber Footer Start banner positioning and spacing, especially for left spacing adjustments.
* Backward Compatibility: Existing Blabber Footer Start banners default to left alignment and no additional spacing.

## [1.9.6]
* Breaking Change: Removed global sticky footer banner option - sticky behavior is now controlled per individual banner
* Enhancement: Each footer banner now has its own independent sticky setting for granular control
* UI Improvement: Cleaner admin interface with sticky setting only available per banner, not globally
* Backward Compatibility: Removed legacy global sticky setting registration and fallback logic
* User Experience: Users can now mix sticky and non-sticky footer banners in the same location
* Technical Enhancement: Simplified sticky footer logic by removing global setting dependencies

## [1.9.5]
* UI Enhancement: Moved header and footer banner locations to the bottom of the settings page for better organization
* Bug Fix: Fixed sticky footer banner option to properly toggle on/off
* Bug Fix: Fixed wrapper background color fields to load with proper default values (#ffffff for header, #161515 for footer)
* Admin Interface: Improved settings page layout with header/footer sections positioned at the bottom
* User Experience: Enhanced settings page organization with content-related banners at the top and page-level banners at the bottom

## [1.9.4]
* New Feature: Added sticky footer banner option - footer banners can now stick to the bottom of the viewport
* New Feature: Added wrapper background color customization for header and footer banner sections
* Enhancement: Header banner wrapper sections can now have custom background colors (default: #ffffff)
* Enhancement: Footer banner wrapper sections can now have custom background colors (default: #161515)
* UI Enhancement: Added color picker input for wrapper background color selection in admin interface
* CSS Enhancement: Added sticky positioning support with proper z-index and responsive behavior
* CSS Enhancement: Added wrapper div structure with background color support for header and footer banners
* User Experience: Sticky footer banners remain visible when scrolling for better advertisement visibility
* Admin Interface: Added sticky checkbox option specifically for footer banner location
* Responsive Design: Sticky footer banners maintain proper positioning across all device sizes

## [1.9.3]
* New Feature: Added alignment options for header (wp_head) and footer (wp_footer) banner locations
* Enhancement: Header banners can now be aligned left, center, or right within their container
* Enhancement: Footer banners can now be aligned left, center, or right within their container
* Admin Interface: Added alignment dropdown selection for each individual header and footer banner
* CSS Enhancement: Added text-align styling support for banner alignment in iwz-head-banner and iwz-footer-banner wrapper classes
* User Experience: Each banner in header and footer locations can have independent alignment settings
* Backward Compatibility: Existing banners default to left alignment to maintain current behavior

## [1.9.2]
* Enhancement: Added automatic `code-block` class to all banner container wrappers for improved age verification compatibility
* Age Verification: All banners now automatically include both their location-specific class AND `code-block` class
* CSS Enhancement: Updated age verification selectors to target the new automatic `code-block` class pattern
* Compatibility: Enhanced integration with JavaScript-based age verification systems that target `.code-block.d-none`
* System Design: Implements additive class system where banners receive: location class + `code-block` + any custom classes
* Technical Note: This ensures consistent age verification functionality across all banner locations without requiring manual class configuration

## [1.9.1]
* Enhancement: Added age verification CSS support for improved compatibility with JavaScript-based age verification modals
* Important: Age verification functionality requires JavaScript modal implementation that adds/removes the `d-none` class
* New CSS Classes: Added `.d-none` class with high specificity to ensure proper banner hiding
* Compatibility: Improved integration with Ad Inserter and custom age verification systems using JavaScript
* CSS Enhancement: Added specific targeting for `.code-block.d-none` and `.iwz-blabber-footer-banner.d-none` classes
* User Experience: Banners now properly hide when age verification restrictions apply via JavaScript class manipulation
* Technical Requirement: This feature only works with age verification systems that implement JavaScript modal functionality

## [1.9.0]
* Major Enhancement: Implemented comprehensive default wrapper class system for ALL banner locations
* New Feature: Additive wrapper class system - users can add custom classes while keeping defaults
* Default Classes: Each location now has unique default CSS classes:
  - `content_wrap_inside` → `iwz-blabber-header-banner`
  - `blabber_footer_start` → `iwz-blabber-footer-banner`
  - `wp_head` → `iwz-head-banner`
  - `wp_footer` → `iwz-footer-banner`
  - `dynamic_sidebar_before` → `iwz-sidebar-banner`
  - `wp_nav_menu_items` → `iwz-menu-banner`
  - `the_content` → `iwz-content-banner`
* CSS Enhancement: Added comprehensive default styling for all new wrapper classes
* User Experience: Improved setup experience with automatic default styling for all banner locations
* Backward Compatibility: Maintained support for existing `iwz-header-banner` class while adding new defaults
* Admin Interface: Updated descriptions to reflect new default behavior for all locations
* Code Quality: Enhanced `wrap_banner_html()` method to support additive class system with location-based defaults

## [1.8.3]
* Enhancement: Content Wrap Inside banner location now defaults to 'iwz-header-banner' wrapper class
* Improvement: New content_wrap_inside banners automatically include predefined styling wrapper
* User Experience: Simplified setup for content wrap banners with sensible default CSS class

## [1.8.2]
* New Feature: Added div wrapper functionality for all banner types with customizable CSS classes
* Enhancement: Each banner can now be wrapped in a div element with a custom CSS class
* Admin Interface: Added "Wrapper CSS Class" input field to both content banners and location banners
* Data Structure: Enhanced banner data to include wrapper_class field with proper sanitization
* Code Quality: Added wrap_banner_html() utility method for consistent banner wrapping across all locations
* Backward Compatibility: Maintained full compatibility with existing banners while adding wrapper functionality
* JavaScript: Updated dynamic banner addition to support wrapper class input for new banners

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
