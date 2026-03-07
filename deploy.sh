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
#
# The script will stop immediately if any command fails
# and will display which command caused the failure.
# ---------------------------------------------------


# ---------------------------------------------------
# Error handling
# ---------------------------------------------------

# Stop the script if any command fails
set -e

# Catch failures in pipelines
set -o pipefail

# Show the exact command and line number that failed
trap 'echo "ERROR: Command \"$BASH_COMMAND\" failed on line $LINENO"; exit 1' ERR


# ---------------------------------------------------
# Variables
# ---------------------------------------------------

REPO_ROOT="$HOME/git/IT490-Project-Repo"
WEBROOT="/var/www/html"

# These are the project folders we want to deploy
PROJECT_FOLDERS=("frontend" "integration" "database" "backend" "public")


# ---------------------------------------------------
# 1) Safety checks
# ---------------------------------------------------

echo "Starting deployment..."
echo "Repository root: $REPO_ROOT"
echo "Web root: $WEBROOT"

# Make sure the repo root exists
if [[ ! -d "$REPO_ROOT" ]]; then
    echo "ERROR: Repository directory $REPO_ROOT does not exist."
    exit 1
fi

# Make sure the Apache web root exists
if [[ ! -d "$WEBROOT" ]]; then
    echo "ERROR: Web root directory $WEBROOT does not exist."
    exit 1
fi

# Make sure the landing page exists
if [[ ! -f "$REPO_ROOT/index.php" ]]; then
    echo "ERROR: Landing page $REPO_ROOT/index.php does not exist."
    exit 1
fi


# ---------------------------------------------------
# 2) Delete old site folders
#
# Remove existing copies from Apache so we don't end
# up with nested folders or outdated files.
# ---------------------------------------------------

echo "Removing old site directories..."

for folder in "${PROJECT_FOLDERS[@]}"; do
    sudo rm -rf "$WEBROOT/$folder"
done


# ---------------------------------------------------
# 3) Copy fresh folders from the Git repository
#
# The -a flag (archive mode) preserves permissions,
# timestamps, and directory structure.
# ---------------------------------------------------

echo "Copying project folders..."

for folder in "${PROJECT_FOLDERS[@]}"; do
    if [[ -d "$REPO_ROOT/$folder" ]]; then
        sudo cp -a "$REPO_ROOT/$folder" "$WEBROOT/"
    else
        echo "WARNING: Folder $REPO_ROOT/$folder does not exist. Skipping."
    fi
done

# Copy the landing page to the Apache root
echo "Copying landing page..."
sudo cp "$REPO_ROOT/index.php" "$WEBROOT/index.php"


# ---------------------------------------------------
# 4) Fix ownership
#
# Apache runs under the "www-data" user. We give that
# user ownership of the files so Apache can read them.
# ---------------------------------------------------

echo "Setting Apache ownership..."
sudo chown -R www-data:www-data "$WEBROOT"


# ---------------------------------------------------
# 5) Reload Apache
#
# Reload the web server so it picks up the new files
# without fully restarting the service.
# ---------------------------------------------------

echo "Reloading Apache..."
sudo systemctl reload apache2

echo "Deployment complete!"