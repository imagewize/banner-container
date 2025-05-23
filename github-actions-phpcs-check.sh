#!/usr/bin/env bash

# This script helps diagnose differences between local PHPCS and GitHub Actions PHPCS

# Create a report directory if it doesn't exist
REPORT_DIR="phpcs-reports"
mkdir -p "$REPORT_DIR"

echo "Checking PHPCS version..."
vendor/bin/phpcs --version > "$REPORT_DIR/version.txt"

echo "Running standard PHPCS check (same as composer phpcs)..."
vendor/bin/phpcs --standard=WordPress --extensions=php --ignore=*/vendor/* . > "$REPORT_DIR/standard-report.txt" || true

echo "Running PHPCS with verbose output to see which files have issues..."
vendor/bin/phpcs --standard=WordPress --extensions=php --ignore=*/vendor/* -v . > "$REPORT_DIR/verbose-report.txt" || true

# Sometimes GitHub Actions uses a different set of default sniffs
# Let's check using explicitly specified rules
echo "Running with explicit WordPress-Extra standard..."
vendor/bin/phpcs --standard=WordPress-Extra --extensions=php --ignore=*/vendor/* -v . > "$REPORT_DIR/wordpress-extra-report.txt" || true

echo "Generating report of unfixed issues..."
echo "===============================================" > "$REPORT_DIR/issues-summary.txt"
echo "REMAINING WORDPRESS CODING STANDARDS ISSUES" >> "$REPORT_DIR/issues-summary.txt"
echo "===============================================" >> "$REPORT_DIR/issues-summary.txt"
echo "" >> "$REPORT_DIR/issues-summary.txt"

# Group issues by type
echo "1. ESCAPING ISSUES:" >> "$REPORT_DIR/issues-summary.txt"
vendor/bin/phpcs --standard=WordPress --sniffs=WordPress.Security.EscapeOutput --extensions=php --ignore=*/vendor/* . | grep -v "^FILE:" | grep -v "^-\+" >> "$REPORT_DIR/issues-summary.txt" || true
echo "" >> "$REPORT_DIR/issues-summary.txt"

echo "2. YODA CONDITIONS ISSUES:" >> "$REPORT_DIR/issues-summary.txt"
vendor/bin/phpcs --standard=WordPress --sniffs=WordPress.PHP.YodaConditions --extensions=php --ignore=*/vendor/* . | grep -v "^FILE:" | grep -v "^-\+" >> "$REPORT_DIR/issues-summary.txt" || true
echo "" >> "$REPORT_DIR/issues-summary.txt"

echo "3. TRANSLATION ISSUES:" >> "$REPORT_DIR/issues-summary.txt"
vendor/bin/phpcs --standard=WordPress --sniffs=WordPress.WP.I18n --extensions=php --ignore=*/vendor/* . | grep -v "^FILE:" | grep -v "^-\+" >> "$REPORT_DIR/issues-summary.txt" || true
echo "" >> "$REPORT_DIR/issues-summary.txt"

echo "4. DOC COMMENT ISSUES:" >> "$REPORT_DIR/issues-summary.txt"
vendor/bin/phpcs --standard=WordPress --sniffs=Squiz.Commenting,PEAR.Commenting --extensions=php --ignore=*/vendor/* . | grep -v "^FILE:" | grep -v "^-\+" >> "$REPORT_DIR/issues-summary.txt" || true
echo "" >> "$REPORT_DIR/issues-summary.txt"

echo "Reports saved to $REPORT_DIR directory."
echo ""
echo "To fix remaining issues, you can:"
echo "1. Run 'composer phpcs-fix' again"
echo "2. Fix the most common issues manually by reviewing $REPORT_DIR/issues-summary.txt"
echo "3. For specific files, run: vendor/bin/phpcbf --standard=WordPress path/to/file.php"
echo ""
echo "Happy coding!"
