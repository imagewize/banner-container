#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress/}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

# Make sure a directory exists before attempting to write to it
ensure_dir_exists() {
    local dir=$(dirname "$1")
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
    fi
}

# Function to check if SVN is installed
has_svn() {
    if command -v svn >/dev/null 2>&1; then
        return 0
    else
        return 1
    fi
}

# Function to download files from SVN repository via HTTP when SVN isn't available
download_svn_files() {
    local SVN_URL=$1
    local TARGET_DIR=$2
    
    # Create target directory if it doesn't exist
    mkdir -p "$TARGET_DIR"
    
    echo "SVN not installed. Using alternative download method from SVN HTTP interface..."
    
    # Strip trailing slashes from SVN URL
    SVN_URL=$(echo $SVN_URL | sed 's/\/$//')
    
    # Create a temporary file for the directory listing
    local TMP_LIST="$TMPDIR/svn-listing-$RANDOM.txt"
    
    # Download the directory listing
    download "$SVN_URL/" "$TMP_LIST"
    
    # Parse the HTML to extract file names (basic approach)
    if [ -s "$TMP_LIST" ]; then
        # Extract hrefs from the HTML that aren't parent directory links
        grep -o 'href="[^"]*"' "$TMP_LIST" | grep -v '\.\.' | sed 's/href="//' | sed 's/"//' | while read -r file; do
            # Skip if it's a directory (ends with /)
            if [[ $file == */ ]]; then
                # Recursive call for subdirectories
                download_svn_files "$SVN_URL/$file" "$TARGET_DIR/$(basename "$file")"
            else
                # Download individual file
                echo "Downloading $file"
                download "$SVN_URL/$file" "$TARGET_DIR/$file"
            fi
        done
    else
        echo "Failed to get file listing from $SVN_URL"
        
        # Fallback: Try to download wp-tests-config-sample.php directly
        if [[ $SVN_URL == *"/wp-tests-config-sample.php"* ]]; then
            download "$SVN_URL" "$TARGET_DIR/$(basename "$SVN_URL")"
        fi
    fi
    
    # Clean up
    rm -f "$TMP_LIST"
}

# Safely download a file ensuring its directory exists
safe_download() {
    local source=$1
    local target=$2
    
    # Make sure the target directory exists
    ensure_dir_exists "$target"
    
    # Download the file
    download "$source" "$target"
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\$ ]]; then
    WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+\$ ]]; then
    if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
        # version x.x.0 means the first release of the major version, so strip off the .0 and download version x.x
        WP_TESTS_TAG="tags/${WP_VERSION%??}"
    else
        WP_TESTS_TAG="tags/$WP_VERSION"
    fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
    WP_TESTS_TAG="trunk"
else
    # http serves a single offer, whereas https serves multiple. we only want one
    download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
    grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json || true
    LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
    if [[ -z "$LATEST_VERSION" ]]; then
        echo "Latest WordPress version could not be found"
        exit 1
    fi
    WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

# Use the correct tag for WordPress 5.8
if [[ $WP_VERSION == "5.8" ]]; then
    # For WordPress 5.8, we need to use a specific tag
    WP_TESTS_TAG="tags/5.8"
fi

set -ex

install_wp() {

	if [ -d $WP_CORE_DIR ]; then
		return;
	fi

	mkdir -p $WP_CORE_DIR

	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		mkdir -p $TMPDIR/wordpress-nightly
		download https://wordpress.org/nightly-builds/wordpress-latest.zip  $TMPDIR/wordpress-nightly/wordpress-nightly.zip
		unzip -q $TMPDIR/wordpress-nightly/wordpress-nightly.zip -d $TMPDIR/wordpress-nightly/
		mv $TMPDIR/wordpress-nightly/wordpress/* $WP_CORE_DIR
	else
		if [ $WP_VERSION == 'latest' ]; then
			local ARCHIVE_NAME='latest'
		else
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi
		download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  $TMPDIR/wordpress.tar.gz
		tar --strip-components=1 -zxmf $TMPDIR/wordpress.tar.gz -C $WP_CORE_DIR
	fi

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php $WP_CORE_DIR/wp-content/db.php
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i.bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d $WP_TESTS_DIR ]; then
		# set up testing suite
		mkdir -p $WP_TESTS_DIR
		
		if has_svn; then
			svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes
			svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WP_TESTS_DIR/data
		else
			echo "SVN not installed. Using alternative download method..."
			
			# First create all required directories
			mkdir -p $WP_TESTS_DIR/includes
			mkdir -p $WP_TESTS_DIR/data
			mkdir -p $WP_TESTS_DIR/data/plugins
			
			# Array of common files to download
			declare -a files=(
				"bootstrap.php"
				"factory.php"
				"testcase.php"
				"trac.php"
				"utils.php"
				"exceptions.php"
				"abstract-testcase.php"
				"mock-mailer.php"
				"install.php"
			)
			
			# Download each file with proper error handling
			for file in "${files[@]}"; do
				target_file="$WP_TESTS_DIR/includes/$file"
				source_url="https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/$file"
				
				echo "Downloading $file from $source_url"
				safe_download "$source_url" "$target_file"
				
				# Check if file was downloaded successfully
				if [ ! -s "$target_file" ]; then
					echo "Warning: Failed to download $file. Testing may be incomplete."
					# Try to get it from trunk as fallback
					echo "Trying from trunk instead..."
					safe_download "https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/$file" "$target_file"
				fi
			done
			
			# Create the plugins directory and download a sample data file 
			mkdir -p "$WP_TESTS_DIR/data/plugins"
			safe_download "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/plugins/hello.php" "$WP_TESTS_DIR/data/plugins/hello.php"
			
			# Check if file was downloaded successfully
			if [ ! -s "$WP_TESTS_DIR/data/plugins/hello.php" ]; then
				echo "Warning: Failed to download hello.php plugin. Trying from trunk..."
				safe_download "https://develop.svn.wordpress.org/trunk/tests/phpunit/data/plugins/hello.php" "$WP_TESTS_DIR/data/plugins/hello.php"
			fi
        fi
    fi

    if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
        safe_download "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"
		
		# Check if the config sample was successfully downloaded
		if [ ! -s "$WP_TESTS_DIR/wp-tests-config.php" ]; then
			echo "Failed to download wp-tests-config-sample.php. Trying from trunk..."
			safe_download "https://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"
		fi
		
		# remove all forward slashes in the end
		WP_CORE_DIR=$(echo $WP_CORE_DIR | sed "s:/\+$::")
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi
}

install_db() {

	if [ ${SKIP_DB_CREATE} = "true" ]; then
		return 0
	fi

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_wp
install_test_suite
install_db
