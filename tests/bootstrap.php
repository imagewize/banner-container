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

// First, we need to load the WordPress test environment.
// This is typically set up with the WordPress-develop project.
// If you're running tests locally, you'll need to set up the test environment.

// Path to the WordPress test environment.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// If the tests directory doesn't exist, try to create it.
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Warning: The WordPress test environment is not set up correctly.\n";
	echo "Please see https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/ for more information.\n";
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/banner-iframe-plugin.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
