#!/bin/bash

# ---------------------------------------------------
# install_bundle.sh
#
# This script installs a bundle onto this machine.
#
# NOTE:
#   If you see "Permission denied", run:
#   chmod +x install_bundle.sh
#
# Usage:
#   ./install_bundle.sh <bundle.zip> [install_target] [db_name] [machine]
#
# Defaults:
#   [install_target] = /var/www/html
#   [db_name]        = testdb
#   [machine]        = current username (whoami)
#
# Example (using defaults):
#   ./install_bundle.sh IT490-Test-Bundle-2.0.0.zip
#
# Example (custom values):
#   ./install_bundle.sh IT490-Test-Bundle-2.0.0.zip /var/www/html guiltyspark mushran
#
# Bundle layout:
#   bundle/
#     ├── version.txt
#     ├── commands.txt (optional)
#     └── files/
#         ├── index.php
#         └── project folders (frontend, backend, etc.)
#
# NOTE:
#   1) This script does NOT restart services on its own.
#      If you need to restart something, put those
#      commands in commands.txt.
#
#   2) The bundle version comes from version.txt
#      inside the zip file.
#
#   3) The zip file name itself can be anything.
#      The script only stores that file name in the
#      bundles table for tracking purposes.
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

BUNDLE_ZIP="$1"
WEBROOT="${2:-/var/www/html}"
DB_NAME="${3:-testdb}"
MACHINE="${4:-$(whoami)}"

TEMP_DIR="/tmp/it490_bundle_install"
BUNDLE_ROOT="$TEMP_DIR/bundle"
FILES_DIR="$BUNDLE_ROOT/files"
VERSION_FILE="$BUNDLE_ROOT/version.txt"
COMMANDS_FILE="$BUNDLE_ROOT/commands.txt"

BUNDLE_NAME="$(basename "$BUNDLE_ZIP")"


# ---------------------------------------------------
# 1) Basic checks
# ---------------------------------------------------

echo "Starting install..."

if [[ -z "$BUNDLE_ZIP" ]]; then
    echo "No bundle provided."
    echo "Usage: ./install_bundle.sh /path/to/bundle.zip [install_target] [db_name] [machine]"
    exit 1
fi

if [[ ! -f "$BUNDLE_ZIP" ]]; then
    echo "Bundle not found: $BUNDLE_ZIP"
    exit 1
fi

if [[ ! -d "$WEBROOT" ]]; then
    echo "Install location not found: $WEBROOT"
    exit 1
fi

if ! command -v unzip >/dev/null 2>&1; then
    echo "unzip is not installed."
    exit 1
fi

if ! command -v mysql >/dev/null 2>&1; then
    echo "mysql is not installed."
    exit 1
fi


# ---------------------------------------------------
# 2) Set up temp workspace
# ---------------------------------------------------

echo "Setting up temporary workspace..."
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"


# ---------------------------------------------------
# 3) Extract bundle
# ---------------------------------------------------

echo "Extracting bundle..."
unzip -q "$BUNDLE_ZIP" -d "$TEMP_DIR"


# ---------------------------------------------------
# 4) Check bundle structure
# ---------------------------------------------------

echo "Checking bundle contents..."

if [[ ! -d "$BUNDLE_ROOT" || ! -d "$FILES_DIR" ]]; then
    echo "Bundle structure is not correct."
    exit 1
fi

if [[ ! -f "$VERSION_FILE" ]]; then
    echo "version.txt is missing."
    exit 1
fi

VERSION=$(tr -d '[:space:]' < "$VERSION_FILE")

if [[ -z "$VERSION" ]]; then
    echo "version.txt is empty."
    exit 1
fi

echo "Bundle version: $VERSION"
echo "Bundle file name: $BUNDLE_NAME"
echo "Installing to: $WEBROOT"
echo "Database: $DB_NAME"
echo "Machine: $MACHINE"


# ---------------------------------------------------
# 5) Copy project folders
# ---------------------------------------------------

echo "Copying project folders..."

FOUND_FOLDER=false

for item in "$FILES_DIR"/*; do
    if [[ -d "$item" ]]; then
        folder_name=$(basename "$item")
        echo "Installing folder: $folder_name"
        sudo rm -rf "$WEBROOT/$folder_name"
        sudo cp -a "$item" "$WEBROOT/"
        FOUND_FOLDER=true
    fi
done

if [[ "$FOUND_FOLDER" == false ]]; then
    echo "No folders found to install."
fi


# ---------------------------------------------------
# 6) Copy index.php (if included)
# ---------------------------------------------------

if [[ -f "$FILES_DIR/index.php" ]]; then
    echo "Installing index.php"
    sudo cp "$FILES_DIR/index.php" "$WEBROOT/index.php"
else
    echo "No index.php in bundle (skipping)"
fi


# ---------------------------------------------------
# 7) Run commands (if provided)
# ---------------------------------------------------

if [[ -f "$COMMANDS_FILE" ]]; then
    echo "Running commands from commands.txt..."

    while IFS= read -r command || [[ -n "$command" ]]; do
        [[ -z "$command" || "$command" =~ ^# ]] && continue
        echo "Running: $command"
        eval "$command"
    done < "$COMMANDS_FILE"
else
    echo "No commands.txt found (skipping)"
fi


# ---------------------------------------------------
# 8) Fix permissions
# ---------------------------------------------------

echo "Fixing file permissions..."
sudo chown -R www-data:www-data "$WEBROOT"


# ---------------------------------------------------
# 9) Record deployment in bundles table
# ---------------------------------------------------

echo "Recording deployment in bundles table..."

sudo mysql -u root -D "$DB_NAME" -e "
INSERT INTO bundles (version_number, machine, bundle_name, status)
VALUES ('$VERSION', '$MACHINE', '$BUNDLE_NAME', 'new');
"


# ---------------------------------------------------
# 10) Clean up
# ---------------------------------------------------

echo "Cleaning up temp files..."
rm -rf "$TEMP_DIR"


# ---------------------------------------------------
# Done
# ---------------------------------------------------

echo "Install complete! Version: $VERSION"