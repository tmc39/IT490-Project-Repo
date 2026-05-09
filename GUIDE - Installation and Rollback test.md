# Deployment Bundle Demo Guide

This document explains how to demonstrate the deployment installation and rollback system.

---

# Main Scripts

| Script | Purpose |
|---|---|
| `install_bundle.sh` | Installs a deployment bundle |
| `rollback_bundle.sh` | Restores the latest passed version |

---

# Demo Overview

The basic demo flow is:

1. Install a deployment bundle
2. Verify deployment was recorded
3. Mark deployment as passed
4. Install a newer version
5. Roll back to the previous passed version

---

# TERMINAL #1 — Watch Deployment Database

Run:

```bash
watch -n 2 'sudo mysql -u root -p testdb -e "SELECT * FROM bundles;"'
```

## What this does

- `watch`
  Re-runs a command repeatedly

- `-n 2`
  Refreshes every 2 seconds

- `SELECT * FROM bundles`
  Displays all deployment records

---

# TERMINAL #2 — Run Deployment Scripts

This terminal is used to install and rollback bundles.

---

# Part 1 — Install Bundle

## Run:

```bash
./install_bundle.sh IT490-Test-Bundle-1.0.0.zip
```

---

## What this does

1. Extracts the bundle
2. Reads the bundle version
3. Copies project files into `/var/www/html`
4. Runs `commands.txt` if it exists
5. Records the deployment in the `bundles` table

---

## Expected Output

```text
Install complete! Version: 1.0.0
```

---

# Part 2 — Verify Deployment Was Recorded

## In TERMINAL #1

You should now see something similar to:

```text
+-----------+----------------+---------+-----------------------------+--------+
| bundle_id | version_number | machine | bundle_name                 | status |
+-----------+----------------+---------+-----------------------------+--------+
| 1         | 1.0.0          | mushran | IT490-Test-Bundle-1.0.0.zip | new    |
+-----------+----------------+---------+-----------------------------+--------+
```

---

# Part 3 — Mark Deployment As Passed

## Run:

```bash
sudo mysql -u root -p testdb -e "
UPDATE bundles
SET status = 'passed'
WHERE version_number = '1.0.0'
AND machine = 'mushran';
"
```

---

## What this does

- `UPDATE bundles`
  Updates a deployment record

- `SET status = 'passed'`
  Marks the deployment as stable

- `WHERE version_number = '1.0.0'`
  Finds the matching version

- `AND machine = 'mushran'`
  Makes sure only this machine's deployment is updated

---

# Part 4 — Install Newer Version

## Edit `test_bundle/bundle/version.txt`

Change:

```text
1.0.0
```

to:

```text
2.0.0
```

---

## Optional: Modify `index.php`

Example:

```php
<?php
echo "This is version 2.0.0";
?>
```

---

## Install New Version

```bash
./install_bundle.sh IT490-Test-Bundle-2.0.0.zip
```

---

## What this does

1. Installs the newer bundle version
2. Updates the live files in `/var/www/html`
3. Adds another deployment record into the `bundles` table

---

## Verify Database

You should now see both versions:

```text
1.0.0
2.0.0
```

---

# Part 5 — Rollback Demo

## Run:

```bash
./rollback_bundle.sh
```

---

## What this does

1. Looks up the latest deployment marked as `passed`
2. Finds the matching bundle zip
3. Removes current application files
4. Reinstalls the older stable version
5. Marks the restored deployment as `passed`

---

## Expected Output

```text
Rollback complete.
Restored bundle: IT490-Test-Bundle-1.0.0.zip
```

---

# Part 6 — Verify Rollback Worked

## Check `/var/www/html/index.php`

Run:

```bash
cat /var/www/html/index.php
```

## What this does

- `cat`
  Displays the contents of a file

- `/var/www/html/index.php`
  Shows the currently deployed landing page

You should now see the older version restored.

---

# Useful Commands

## Check Deployment Table

```bash
sudo mysql -u root -p testdb -e "SELECT * FROM bundles;"
```

## What this does

- `mysql`
  Opens a MySQL command

- `-u root`
  Uses the root MySQL account

- `-p`
  Prompts for the password

- `SELECT * FROM bundles`
  Displays all deployment records

---

## Describe Deployment Table

```bash
sudo mysql -u root -p testdb -e "DESCRIBE bundles;"
```

## What this does

- `DESCRIBE bundles`
  Displays the structure of the bundles table

---

## Make Scripts Executable

```bash
chmod +x install_bundle.sh
chmod +x rollback_bundle.sh
```

## What this does

- `chmod`
  Changes file permissions

- `+x`
  Makes the scripts executable

---

# Bundle Structure Expected By Installer

```text
bundle/
├── version.txt
├── commands.txt
└── files/
    ├── frontend/
    ├── backend/
    ├── integration/
    ├── public/
    └── database/
```

---

# Important Notes

1. The bundle version comes from:

```text
bundle/version.txt
```

2. The zip file name itself can be anything.

3. Rollback only restores versions marked as:

```text
passed
```

4. The installer uses a temporary extraction folder:

```text
/tmp/it490_bundle_install
```

5. Deployment history is tracked in the `bundles` table.