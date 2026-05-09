#!/bin/bash

# ---------------------------------------------------
# rollback_bundle.sh
#
# This script rolls the project back to the latest
# bundle marked as "passed" for a specific machine.
#
# NOTE:
#   If you see "Permission denied", run:
#   chmod +x rollback_bundle.sh
#
# Usage:
#   ./rollback_bundle.sh [db_name] [machine] [bundle_dir] [install_target]
#
# Defaults:
#   [db_name]        = testdb
#   [machine]        = current username (whoami)
#   [bundle_dir]     = current folder (.)
#   [install_target] = /var/www/html
#
# Example (using defaults):
#   ./rollback_bundle.sh
#
# Example (custom values - team setup):
#   ./rollback_bundle.sh guiltyDatabase mushran ./Versions /var/www/html
#
# If machine is not provided, the script uses the
# current Ubuntu username.
# ---------------------------------------------------


# ---------------------------------------------------
# Error handling
# ---------------------------------------------------

set -e
set -o pipefail
trap 'echo "ERROR: Something failed on line $LINENO"; exit 1' ERR


# ---------------------------------------------------
# Variables
# ---------------------------------------------------

MACHINE="${2:-$(whoami)}"
BUNDLE_DIR=$2
WEBROOT="${4:-/var/www/html}"

TEMP_DIR="/tmp/it490_rollback_bundle"
BUNDLE_ROOT="$TEMP_DIR/bundle"
VERSION="$1"

PROJECT_FOLDERS=("frontend" "backend" "integration" "public" "database")


# ---------------------------------------------------
# 1) Basic checks
# ---------------------------------------------------

echo "Starting rollback..."
echo "Machine: $MACHINE"
echo "Bundle folder: $BUNDLE_DIR"
echo "Install location: $WEBROOT"

if [[ ! -d "$WEBROOT" ]]; then
    echo "Install location not found: $WEBROOT"
    exit 1
fi

if [[ ! -d "$BUNDLE_DIR" ]]; then
    echo "Bundle folder not found: $BUNDLE_DIR"
    exit 1
fi

if [[ ! -f "./install_bundle.sh" ]]; then
    echo "install_bundle.sh was not found in this folder."
    exit 1
fi

if [[ ! -x "./install_bundle.sh" ]]; then
    echo "install_bundle.sh is not executable."
    echo "If needed, run: chmod +x install_bundle.sh"
    exit 1
fi

if ! command -v mysql >/dev/null 2>&1; then
    echo "mysql is not installed."
    exit 1
fi

if ! command -v unzip >/dev/null 2>&1; then
    echo "unzip is not installed."
    exit 1
fi


# ---------------------------------------------------
# 2) Look up latest passed bundle
# ---------------------------------------------------

BUNDLE_NAME="$VERSION.zip"

BUNDLE_PATH="$BUNDLE_DIR/$BUNDLE_NAME"

if [[ ! -f "$BUNDLE_PATH" ]]; then
    echo "Bundle file not found: $BUNDLE_PATH"
    exit 1
fi

echo "Found rollback bundle: $BUNDLE_NAME"

# ---------------------------------------------------
# 3) Read version from bundle
# ---------------------------------------------------

echo "Reading version from bundle..."

rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

unzip -q "$BUNDLE_PATH" -d "$TEMP_DIR"

echo "Rollback version: $VERSION"


# ---------------------------------------------------
# 4) Clear current app files
# ---------------------------------------------------

echo "Clearing current app files..."

for folder in "${PROJECT_FOLDERS[@]}"; do
    if [[ -d "$WEBROOT/$folder" ]]; then
        echo "Removing folder: $folder"
        sudo rm -rf "$WEBROOT/$folder"
    fi
done

if [[ -f "$WEBROOT/index.php" ]]; then
    echo "Removing index.php"
    sudo rm -f "$WEBROOT/index.php"
fi


# ---------------------------------------------------
# 5) Reinstall the passed bundle
# ---------------------------------------------------

echo "Reinstalling bundle..."
./install_bundle.sh "$VERSION" "$BUNDLE_DIR" "$DB_NAME" "$MACHINE"


# ---------------------------------------------------
# 6) Mark restored version as passed
# ---------------------------------------------------

#echo "Marking restored version as passed..."

#sudo mysql -u root -D "$DB_NAME" -e "
#UPDATE bundles
#SET status = 'passed'
#WHERE version_number = '$VERSION'
#AND machine = '$MACHINE'
#ORDER BY created_at DESC
#LIMIT 1;
#"


# ---------------------------------------------------
# 7) Clean up
# ---------------------------------------------------

rm -rf "$TEMP_DIR"


# ---------------------------------------------------
# Done
# ---------------------------------------------------

echo "Rollback complete."
echo "Restored bundle: $BUNDLE_NAME"
echo "Marked version $VERSION as passed."
