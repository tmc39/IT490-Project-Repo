#!/bin/bash

# ---------------------------------------------------
# install_bundle.sh
#
# Installs a deployment bundle onto this VM.
#
# NOTE:
#   If you see "Permission denied", run:
#   chmod +x install_bundle.sh
#
# Bundle structure:
#   bundle/
#     ├── version.txt
#     ├── commands.txt (optional)
#     └── files/
#         ├── index.php
#         ├── frontend/
#         ├── backend/
#         ├── integration/
#         ├── public/
#         └── database/
# ---------------------------------------------------


# ---------------------------------------------------
# Error handling
# ---------------------------------------------------

set -e
set -o pipefail
trap 'echo "ERROR: Command \"$BASH_COMMAND\" failed on line $LINENO"; exit 1' ERR


# ---------------------------------------------------
# Variables
# ---------------------------------------------------

BUNDLE_ZIP="$1"
WEBROOT="/var/www/html"
TEMP_DIR="/tmp/it490_bundle_install"
BUNDLE_ROOT="$TEMP_DIR/bundle"
FILES_DIR="$BUNDLE_ROOT/files"
VERSION_FILE="$BUNDLE_ROOT/version.txt"
COMMANDS_FILE="$BUNDLE_ROOT/commands.txt"

PROJECT_FOLDERS=("frontend" "backend" "integration" "public" "database")


# ---------------------------------------------------
# 1) Safety checks
# ---------------------------------------------------

echo "Starting bundle installation..."

if [[ -z "$BUNDLE_ZIP" ]]; then
    echo "ERROR: No bundle provided"
    exit 1
fi

if [[ ! -f "$BUNDLE_ZIP" ]]; then
    echo "ERROR: Bundle not found: $BUNDLE_ZIP"
    exit 1
fi

if [[ ! -d "$WEBROOT" ]]; then
    echo "ERROR: Webroot not found: $WEBROOT"
    exit 1
fi

if ! command -v unzip >/dev/null 2>&1; then
    echo "ERROR: unzip not installed"
    exit 1
fi


# ---------------------------------------------------
# 2) Prepare temp folder
# ---------------------------------------------------

echo "Preparing workspace..."
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"


# ---------------------------------------------------
# 3) Extract bundle
# ---------------------------------------------------

echo "Extracting bundle..."
unzip -q "$BUNDLE_ZIP" -d "$TEMP_DIR"


# ---------------------------------------------------
# 4) Verify bundle structure
# ---------------------------------------------------

echo "Checking bundle structure..."

if [[ ! -d "$BUNDLE_ROOT" || ! -d "$FILES_DIR" ]]; then
    echo "ERROR: Invalid bundle structure"
    exit 1
fi

if [[ ! -f "$VERSION_FILE" ]]; then
    echo "ERROR: version.txt missing"
    exit 1
fi

VERSION=$(tr -d '[:space:]' < "$VERSION_FILE")

if [[ -z "$VERSION" ]]; then
    echo "ERROR: version.txt empty"
    exit 1
fi

echo "Bundle version: $VERSION"


# ---------------------------------------------------
# 5) Copy bundled project folders
# ---------------------------------------------------

echo "Copying project folders..."

for folder in "${PROJECT_FOLDERS[@]}"; do
    if [[ -d "$FILES_DIR/$folder" ]]; then
        echo "Installing: $folder"
        sudo rm -rf "$WEBROOT/$folder"
        sudo cp -a "$FILES_DIR/$folder" "$WEBROOT/"
    else
        echo "Skipping: $folder"
    fi
done


# ---------------------------------------------------
# 6) Copy index.php if present
# ---------------------------------------------------

if [[ -f "$FILES_DIR/index.php" ]]; then
    echo "Installing index.php"
    sudo cp "$FILES_DIR/index.php" "$WEBROOT/index.php"
fi


# ---------------------------------------------------
# 7) Run commands.txt if present
# ---------------------------------------------------

if [[ -f "$COMMANDS_FILE" ]]; then
    echo "Running commands..."

    while IFS= read -r command || [[ -n "$command" ]]; do
        [[ -z "$command" || "$command" =~ ^# ]] && continue
        echo "Running Command: $command"
        eval "$command"
    done < "$COMMANDS_FILE"
else
    echo "No commands.txt (skipping)"
fi


# ---------------------------------------------------
# 8) Fix permissions
# ---------------------------------------------------

echo "Setting permissions..."
sudo chown -R www-data:www-data "$WEBROOT"


# ---------------------------------------------------
# 9) Reload services
# ---------------------------------------------------

echo "Reloading Apache..."
sudo systemctl reload apache2

if sudo systemctl list-unit-files | grep -q "^testRabbitMQServer.service"; then
    echo "Restarting backend service..."
    sudo systemctl restart testRabbitMQServer.service
fi


# ---------------------------------------------------
# 10) Cleanup
# ---------------------------------------------------

rm -rf "$TEMP_DIR"


# ---------------------------------------------------
# Done
# ---------------------------------------------------

echo "Bundle installation complete! Version: $VERSION"