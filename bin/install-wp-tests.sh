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

has_svn() {
    if command -v svn >/dev/null 2>&1; then
        return 0
    else
        return 1
    fi
}

download_wp_tests() {
    # Instead of using GitHub, download directly from WordPress SVN over HTTP
    echo "Downloading WordPress test suite files..."
    
    # Create directories
    mkdir -p "$WP_TESTS_DIR/includes"
    mkdir -p "$WP_TESTS_DIR/data/plugins"
    
    # Convert SVN path format to HTTP URL format
    local SVN_BASE_URL="https://develop.svn.wordpress.org"
    local HTTP_PATH=""
    
    if [[ $WP_TESTS_TAG == trunk ]]; then
        HTTP_PATH="$SVN_BASE_URL/trunk"
    elif [[ $WP_TESTS_TAG == branches/* ]]; then
        # Replace 'branches/' with 'branches/'
        HTTP_PATH="$SVN_BASE_URL/$WP_TESTS_TAG"
    elif [[ $WP_TESTS_TAG == tags/* ]]; then
        # Replace 'tags/' with 'tags/'
        HTTP_PATH="$SVN_BASE_URL/$WP_TESTS_TAG"
    fi
    
    # Download includes directory files one by one
    echo "Downloading test includes files..."
    download "$HTTP_PATH/tests/phpunit/includes/bootstrap.php" "$WP_TESTS_DIR/includes/bootstrap.php"
    download "$HTTP_PATH/tests/phpunit/includes/factory.php" "$WP_TESTS_DIR/includes/factory.php"
    download "$HTTP_PATH/tests/phpunit/includes/functions.php" "$WP_TESTS_DIR/includes/functions.php"
    download "$HTTP_PATH/tests/phpunit/includes/mock-fs.php" "$WP_TESTS_DIR/includes/mock-fs.php"
    download "$HTTP_PATH/tests/phpunit/includes/mock-image-editor.php" "$WP_TESTS_DIR/includes/mock-image-editor.php"
    download "$HTTP_PATH/tests/phpunit/includes/mock-mailer.php" "$WP_TESTS_DIR/includes/mock-mailer.php"
    download "$HTTP_PATH/tests/phpunit/includes/testcase.php" "$WP_TESTS_DIR/includes/testcase.php"
    download "$HTTP_PATH/tests/phpunit/includes/testcase-canonical.php" "$WP_TESTS_DIR/includes/testcase-canonical.php"
    download "$HTTP_PATH/tests/phpunit/includes/testcase-rest-api.php" "$WP_TESTS_DIR/includes/testcase-rest-api.php"
    download "$HTTP_PATH/tests/phpunit/includes/testcase-rest-controller.php" "$WP_TESTS_DIR/includes/testcase-rest-controller.php"
    download "$HTTP_PATH/tests/phpunit/includes/testcase-rest-post-type-controller.php" "$WP_TESTS_DIR/includes/testcase-rest-post-type-controller.php"
    download "$HTTP_PATH/tests/phpunit/includes/testcase-xmlrpc.php" "$WP_TESTS_DIR/includes/testcase-xmlrpc.php"
    
    # Download basic data files
    echo "Downloading test data files..."
    download "$HTTP_PATH/tests/phpunit/data/plugins/hello.php" "$WP_TESTS_DIR/data/plugins/hello.php"
    
    # Create empty index.php files to match WordPress structure
    echo "<?php
// Silence is golden." > "$WP_TESTS_DIR/data/index.php"
    echo "<?php
// Silence is golden." > "$WP_TESTS_DIR/data/plugins/index.php"
    
    echo "WordPress test suite downloaded successfully via HTTP."
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\- ]]; then
	WP_BRANCH=${WP_VERSION%\-*}
	WP_TESTS_TAG="branches/$WP_BRANCH"

elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
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
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
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
		elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+ ]]; then
			# https serves multiple offers, whereas http serves single.
			download https://api.wordpress.org/core/version-check/1.7/ $TMPDIR/wp-latest.json
			if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
				# version x.x.0 means the first release of the major version, so strip off the .0 and download version x.x
				LATEST_VERSION=${WP_VERSION%??}
			else
				# otherwise, scan the releases and get the most up to date minor version of the major release
				local VERSION_ESCAPED=`echo $WP_VERSION | sed 's/\./\\\\./g'`
				LATEST_VERSION=$(grep -o '"version":"'$VERSION_ESCAPED'[^"]*' $TMPDIR/wp-latest.json | sed 's/"version":"//' | head -1)
			fi
			if [[ -z "$LATEST_VERSION" ]]; then
				local ARCHIVE_NAME="wordpress-$WP_VERSION"
			else
				local ARCHIVE_NAME="wordpress-$LATEST_VERSION"
			fi
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
			echo "SVN not found, using direct download instead..."
			download_wp_tests
		fi
	fi

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
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

	# Check if database exists
	DB_EXISTS=$(mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA -e "SHOW DATABASES LIKE '$DB_NAME'" | grep "$DB_NAME" > /dev/null; echo "$?")
	
	if [ "$DB_EXISTS" -eq 0 ]; then
		echo "Database $DB_NAME already exists, skipping creation"
		# Drop and recreate tables to ensure a clean test environment
		echo "Dropping existing WordPress tables..."
		mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA -e "DROP DATABASE $DB_NAME; CREATE DATABASE $DB_NAME"
	else
		# create database
		mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
	fi
}

install_wp
install_test_suite
install_db
