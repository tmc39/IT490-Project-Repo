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
#   1) Delete old site folders
#   2) Copy fresh folders from the repository
#   3) Fix ownership so Apache (www-data) can serve files
#   4) Reload Apache so changes take effect
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


echo "Starting deployment..."


# ---------------------------------------------------
# 1) Delete old site folders
#
# Remove existing copies from Apache so we don't end
# up with nested folders or outdated files.
# ---------------------------------------------------

echo "Removing old site directories..."

sudo rm -rf /var/www/html/frontend
sudo rm -rf /var/www/html/integration
sudo rm -rf /var/www/html/database
sudo rm -rf /var/www/html/backend
sudo rm -rf /var/www/html/public


# ---------------------------------------------------
# 2) Copy fresh folders from the Git repository
#
# The -a flag (archive mode) preserves permissions,
# timestamps, and directory structure.
# ---------------------------------------------------

echo "Copying project files..."

sudo cp -a ~/git/IT490-Project-Repo/frontend /var/www/html/
sudo cp -a ~/git/IT490-Project-Repo/integration /var/www/html/
sudo cp -a ~/git/IT490-Project-Repo/database /var/www/html/
sudo cp -a ~/git/IT490-Project-Repo/backend /var/www/html/
sudo cp -a ~/git/IT490-Project-Repo/public /var/www/html/

# Copy the landing page to the Apache root
sudo cp ~/git/IT490-Project-Repo/index.php /var/www/html/index.php


# ---------------------------------------------------
# 3) Fix ownership
#
# Apache runs under the "www-data" user. We give that
# user ownership of the files so Apache can read them.
# ---------------------------------------------------

echo "Setting Apache ownership..."

sudo chown -R www-data:www-data /var/www/html


# ---------------------------------------------------
# 4) Reload Apache
#
# Reload the web server so it picks up the new files
# without fully restarting the service.
# ---------------------------------------------------

echo "Reloading Apache..."

sudo systemctl reload apache2


echo "Deployment complete!"