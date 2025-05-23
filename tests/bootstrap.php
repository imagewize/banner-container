<?php
/**
 * Banner iframe plugin
 *
 * @package Banner_Iframe
 */

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

// Forward custom PHPUnit Polyfills autoloader file as required by WordPress.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' );
}

// Give access to tests_add_filter() function.
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
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
    echo "WARNING: WordPress testing functions not available. Make sure the test environment is set up correctly." . PHP_EOL;
}

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
