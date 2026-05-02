#!/bin/bash

# ---------------------------------------------------
# deploy.sh
#
# This script deploys the latest version of our project
# from the Git repository to Apache's web directory.
#
# Project structure (repo):
#   index.php
#   public/
#   frontend/
#   backend/
#   integration/
#   database/
#
# Workflow:
#   1) Safety checks
#   2) Delete old site folders
#   3) Copy fresh folders from the repository
#   4) Fix ownership so Apache (www-data) can serve files
#   5) Reload Apache so changes take effect
#   6) Restart backend service (if present)
#
# The script will stop immediately if any command fails
# and will display which command caused the failure.
# ---------------------------------------------------


# ----------------------
# SCRIPT TROUBLESHOOTING
# ----------------------
# if (PERMISSION DENIED) {chmod +x deploy.sh}


# ---------------------------------------------------
# Error handling
# ---------------------------------------------------

set -e
set -o pipefail

trap 'echo "ERROR: Command \"$BASH_COMMAND\" failed on line $LINENO"; exit 1' ERR


# ---------------------------------------------------
# Variables
# ---------------------------------------------------

REPO_ROOT="$HOME/git/IT490-Project-Repo"
WEBROOT="/var/www/html"

PROJECT_FOLDERS=("frontend" "integration" "database" "backend" "public")


# ---------------------------------------------------
# 1) Safety checks
# ---------------------------------------------------

echo "Starting deployment..."
echo "Repository root: $REPO_ROOT"
echo "Web root: $WEBROOT"

if [[ ! -d "$REPO_ROOT" ]]; then
    echo "ERROR: Repository directory $REPO_ROOT does not exist."
    exit 1
fi

if [[ ! -d "$WEBROOT" ]]; then
    echo "ERROR: Web root directory $WEBROOT does not exist."
    exit 1
fi

if [[ ! -f "$REPO_ROOT/index.php" ]]; then
    echo "ERROR: Landing page $REPO_ROOT/index.php does not exist."
    exit 1
fi


# ---------------------------------------------------
# 2) Delete old site folders
# ---------------------------------------------------

echo "Removing old site directories..."

for folder in "${PROJECT_FOLDERS[@]}"; do
    sudo rm -rf "$WEBROOT/$folder"
done


# ---------------------------------------------------
# 3) Copy fresh folders from the Git repository
# ---------------------------------------------------

echo "Copying project folders..."

for folder in "${PROJECT_FOLDERS[@]}"; do
    if [[ -d "$REPO_ROOT/$folder" ]]; then
        sudo cp -a "$REPO_ROOT/$folder" "$WEBROOT/"
    else
        echo "WARNING: Folder $REPO_ROOT/$folder does not exist. Skipping."
    fi
done

echo "Copying landing page..."
sudo cp "$REPO_ROOT/index.php" "$WEBROOT/index.php"


# ---------------------------------------------------
# 4) Fix ownership
# ---------------------------------------------------

echo "Setting Apache ownership..."
sudo chown -R www-data:www-data "$WEBROOT"


# ---------------------------------------------------
# 5) Reload Apache
# ---------------------------------------------------

echo "Reloading Apache..."
sudo systemctl reload apache2


# ---------------------------------------------------
# 6) Restart backend service (if present)
# ---------------------------------------------------

echo "Checking for backend service..."

if systemctl list-units --type=service | grep -q "testRabbitMQServer.service"; then
    echo "Restarting testRabbitMQServer.service..."
    sudo systemctl restart testRabbitMQServer.service
    echo "Backend service restarted successfully."
else
    echo "Backend service not found. Skipping restart."
fi


echo "Deployment complete!"