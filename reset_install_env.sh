#!/bin/bash

# ---------------------------------------------------
# reset_install_env.sh
#
# This script resets the environment so you can test
# install_bundle.sh from a clean state.
#
# NOTE:
#   If you see "Permission denied", run:
#   chmod +x reset_install_env.sh
#
# Usage:
#   ./reset_install_env.sh [webroot] [temp_dir] [test_bundle_dir] [zip_prefix]
#
# What this script does:
#   1) Clears everything in the install location
#   2) Removes the temp install folder
#   3) Rebuilds the test bundle zip file
#
# NOTE:
#   This does NOT bring back your normal site.
#   Use deploy.sh if you need to restore it.
# ---------------------------------------------------


# ---------------------------------------------------
# Error handling
# ---------------------------------------------------

set -e
set -o pipefail


# ---------------------------------------------------
# Variables
# ---------------------------------------------------

WEBROOT="${1:-/var/www/html}"
TEMP_DIR="${2:-/tmp/it490_bundle_install}"
TEST_BUNDLE_DIR="${3:-test_bundle}"
ZIP_PREFIX="${4:-IT490-Test-Bundle}"

VERSION_FILE="$TEST_BUNDLE_DIR/bundle/version.txt"


# ---------------------------------------------------
# 1) Make sure everything we need is there
# ---------------------------------------------------

if [[ ! -d "$TEST_BUNDLE_DIR" ]]; then
    echo "Test bundle folder is missing: $TEST_BUNDLE_DIR"
    exit 1
fi

if [[ ! -f "$VERSION_FILE" ]]; then
    echo "version.txt is missing: $VERSION_FILE"
    exit 1
fi

if [[ ! -d "$WEBROOT" ]]; then
    echo "Install location not found: $WEBROOT"
    exit 1
fi

if ! command -v zip >/dev/null 2>&1; then
    echo "zip is not installed."
    exit 1
fi

VERSION=$(tr -d '[:space:]' < "$VERSION_FILE")
TEST_BUNDLE_ZIP="${ZIP_PREFIX}-${VERSION}.zip"


# ---------------------------------------------------
# 2) Clear install location
# ---------------------------------------------------

echo "Clearing out: $WEBROOT"
sudo rm -rf "$WEBROOT"/*


# ---------------------------------------------------
# 3) Clear temp folder
# ---------------------------------------------------

echo "Removing temp install files..."
rm -rf "$TEMP_DIR"


# ---------------------------------------------------
# 4) Rebuild bundle
# ---------------------------------------------------

echo "Rebuilding test bundle..."
rm -f "$TEST_BUNDLE_ZIP"
cd "$TEST_BUNDLE_DIR"
zip -r "../$TEST_BUNDLE_ZIP" bundle
cd ..


# ---------------------------------------------------
# Done
# ---------------------------------------------------

echo "Reset complete."
echo "New bundle ready: $TEST_BUNDLE_ZIP"