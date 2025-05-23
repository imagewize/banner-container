<?php
/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

/**
 * PHPUnit bootstrap file
 */

// Path to the WordPress test environment.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Find the appropriate PHPUnit Polyfills path.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	// Check standard composer installation path.
	$composer_polyfills_path = dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

	if ( file_exists( $composer_polyfills_path ) ) {
		define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $composer_polyfills_path );
	} else {
		// Try global vendor paths.
		$global_paths = array(
			dirname( dirname( __DIR__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php',
			'/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php',
		);

		foreach ( $global_paths as $global_path ) {
			if ( file_exists( $global_path ) ) {
				define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $global_path );
				break;
			}
		}

		// If still not defined, just define it and let WP's error handling report the issue.
		if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
			// Look for the polyfills package directory.
			$composer_json = dirname( __DIR__ ) . '/composer.json';
			if ( file_exists( $composer_json ) ) {
				echo "PHPUnit Polyfills not found. Please run: composer require yoast/phpunit-polyfills --dev\n";
			}

			define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' );
		}
	}
}

// Give access to tests_add_filter() function.
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo esc_html( "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" ) . PHP_EOL;
	exit( 1 );
}

// Load the WordPress tests functions.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/banner-iframe-plugin.php';
}

/**
 * Register the plugin loading function.
 *
 * @see tests_add_filter() This function is provided by WordPress testing framework
 */
if ( function_exists( 'tests_add_filter' ) ) {
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
} else {
	echo 'WARNING: WordPress testing functions not available. Make sure the test environment is set up correctly.' . PHP_EOL;
}

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
