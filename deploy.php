<?php
/**
 * Deploy script for WordPress.org Plugin Directory
 *
 * Steps:
 * 1. Run: php deploy.php
 * 2. The script will create a svn-deploy directory with a clean copy of the plugin
 * 3. You'll be prompted for plugin slug and version
 * 4. Plugin will be prepared for SVN
 *
 * Note: You still need to manually commit to SVN
 */

// Exit if run from CLI
if ( 'cli' !== php_sapi_name() ) {
	die( 'This script can only be run from the command line.' );
}

echo "WordPress.org Plugin Directory Deployment Script\n";
echo "----------------------------------------------\n\n";

// Get plugin slug from user input
echo 'Enter the WordPress.org plugin slug: ';
$plugin_slug = trim( fgets( STDIN ) );

if ( empty( $plugin_slug ) ) {
	die( "\nERROR: Plugin slug cannot be empty.\n" );
}

// Get plugin version
echo 'Enter the version to deploy (e.g. 1.0.1): ';
$plugin_version = trim( fgets( STDIN ) );

if ( empty( $plugin_version ) ) {
	die( "\nERROR: Plugin version cannot be empty.\n" );
}

// Set paths
$root_path    = __DIR__;
$deploy_path  = $root_path . '/svn-deploy';
$exclude_list = array(
	'.git',
	'.github',
	'.gitignore',
	'node_modules',
	'bin',
	'tests',
	'vendor',
	'svn-deploy',
	'composer.json',
	'composer.lock',
	'package.json',
	'package-lock.json',
	'phpcs.xml.dist',
	'phpunit.xml',
	'deploy.php',
	'README.md',
	'.DS_Store',
);

// Create clean directory for SVN
if ( file_exists( $deploy_path ) ) {
	echo "Removing existing SVN directory...\n";
	system( "rm -rf $deploy_path" );
}

echo "Creating SVN directory...\n";
mkdir( $deploy_path );

echo "Copying files to SVN directory...\n";
foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root_path ) ) as $filename ) {
	// Skip directories
	if ( $filename->isDir() ) {
		continue;
	}

	// Get the relative path
	$relative_filename = str_replace( $root_path . '/', '', $filename );

	// Skip files/directories in the exclude list
	$skip = false;
	foreach ( $exclude_list as $excluded ) {
		if ( strpos( $relative_filename, $excluded ) === 0 ) {
			$skip = true;
			break;
		}
	}

	if ( $skip ) {
		continue;
	}

	// Create directory if it doesn't exist
	$dest_dir = dirname( $deploy_path . '/' . $relative_filename );
	if ( ! file_exists( $dest_dir ) ) {
		mkdir( $dest_dir, 0755, true );
	}

	// Copy the file
	copy( $filename, $deploy_path . '/' . $relative_filename );
}

echo "Copying readme.txt to SVN directory...\n";
copy( $root_path . '/readme.txt', $deploy_path . '/readme.txt' );

echo "\nDeployment preparation complete!\n";
echo "The plugin files are now ready in the 'svn-deploy' directory.\n\n";
echo "Next steps for SVN deployment:\n";
echo "1. If this is your first time, check out the SVN repository:\n";
echo "   svn co https://plugins.svn.wordpress.org/$plugin_slug svn\n";
echo "2. Copy the contents of 'svn-deploy' to 'svn/trunk/'\n";
echo "3. Create a new tag:\n";
echo "   svn cp svn/trunk svn/tags/$plugin_version\n";
echo "4. Commit the changes:\n";
echo "   cd svn\n";
echo "   svn stat\n";
echo "   svn ci -m \"Release $plugin_version\"\n";

echo "\nDone!\n";
