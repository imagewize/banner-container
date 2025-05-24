<?php
/**
 * Basic test for Banner Container Plugin
 *
 * @package Banner_Container
 */

use PHPUnit\Framework\TestCase;

/**
 * Class BannerContainerBasicTest
 */
class BannerContainerBasicTest extends TestCase {

    /**
     * Test that the plugin constants are defined
     */
    public function test_plugin_constants_defined() {
        $this->assertTrue( defined( 'IWZ_BANNER_CONTAINER_VERSION' ) );
        $this->assertTrue( defined( 'IWZ_BANNER_CONTAINER_PATH' ) );
        $this->assertTrue( defined( 'IWZ_BANNER_CONTAINER_URL' ) );
    }

    /**
     * Test plugin version
     */
    public function test_plugin_version() {
        $this->assertNotEmpty( IWZ_BANNER_CONTAINER_VERSION );
        $this->assertIsString( IWZ_BANNER_CONTAINER_VERSION );
    }

    /**
     * Test plugin path
     */
    public function test_plugin_path() {
        $this->assertNotEmpty( IWZ_BANNER_CONTAINER_PATH );
        $this->assertIsString( IWZ_BANNER_CONTAINER_PATH );
    }

    /**
     * Test plugin URL
     */
    public function test_plugin_url() {
        $this->assertNotEmpty( IWZ_BANNER_CONTAINER_URL );
        $this->assertIsString( IWZ_BANNER_CONTAINER_URL );
    }

    /**
     * Test that basic WordPress functions are available
     */
    public function test_wordpress_functions_available() {
        $this->assertTrue( function_exists( 'add_action' ) );
        $this->assertTrue( function_exists( 'add_filter' ) );
        $this->assertTrue( function_exists( '__' ) );
        $this->assertTrue( function_exists( 'esc_html' ) );
    }
}
