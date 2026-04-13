#!/bin/bash

# ---------------------------------------------------
# reset_install_env.sh
#
# Resets environment for testing install_bundle.sh
#
# NOTE:
#   If you see "Permission denied", run:
#   chmod +x reset_install_env.sh
#
# Steps:
#   1) Clear /var/www/html
#   2) Remove temp bundle folder
#   3) Rebuild test bundle zip
#
# NOTE:
#   Does NOT restore real site (use deploy.sh)
# ---------------------------------------------------


# ---------------------------------------------------
# Error handling
# ---------------------------------------------------

set -e
set -o pipefail


# ---------------------------------------------------
# Variables
# ---------------------------------------------------

WEBROOT="/var/www/html"
TEMP_DIR="/tmp/it490_bundle_install"
TEST_BUNDLE_DIR="test_bundle"
TEST_BUNDLE_ZIP="IT490-Test-Bundle-1.0.0.zip"


# ---------------------------------------------------
# 1) Clear webroot
# ---------------------------------------------------

echo "Clearing webroot..."
sudo rm -rf "$WEBROOT"/*


# ---------------------------------------------------
# 2) Clear temp directory
# ---------------------------------------------------

echo "Clearing temp directory..."
rm -rf "$TEMP_DIR"


# ---------------------------------------------------
# 3) Validate bundle setup
# ---------------------------------------------------

if [[ ! -d "$TEST_BUNDLE_DIR" ]]; then
    echo "ERROR: test_bundle folder missing"
    exit 1
fi

if ! command -v zip >/dev/null 2>&1; then
    echo "ERROR: zip not installed"
    exit 1
fi


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

echo "Environment reset complete."
echo "Bundle ready: $TEST_BUNDLE_ZIP"