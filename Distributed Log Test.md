# Distributed Logging Demo Guide

This document explains two options of demonstrating the distributed logging system.

---

# Option 1: Backend Only Test (Fastest Demo):

This method only requires restarting the backend RabbitMQ listener service.

---

## TERMINAL #1 — Watch Logs Live

Open a terminal and run:

```bash
tail -n 35 -f /var/log/guiltyspark/distributed.log
```

- `tail`
  Displays the end of a file

- `-n 35`
  Shows the latest 35 lines

- `-f`
  Continues watching the file live for new log entries

---

## TERMINAL #2 — Trigger Backend Logging Error

### 1. Edit the backend RabbitMQ listener file

```bash
vim integration/scripts/testRabbitMQServer.php
```

---

### 2. In `requestProcessor()`, change:

```php
case "login":
```

to:

```php
case "loginBAD":
```

### What this does

The frontend still sends:

```php
"type" => "login"
```

However, the backend will no longer recognize that request type, which triggers a distributed log entry.

---

### 3. Restart the backend RabbitMQ listener service

```bash
sudo systemctl restart testRabbitMQServer.service
```

---

### 4. Trigger the error

Attempt to log into the website normally.

---

### 5. Observe the distributed log entry

Terminal #1 should display a new log entry similar to:

```text
{
    timestamp: 2026-05-06 01:40:12,
    level: WARNING,
    source: backend,
    message: Unsupported backend request type: login
}
```

---


# Option 2: Frontend / Apache Redeploy Demo:

This method demonstrates frontend-triggered logging and requires redeploying the application to Apache.

---

## TERMINAL #1 — Watch Logs Live

Open a terminal and run:

```bash
tail -n 35 -f /var/log/guiltyspark/distributed.log
```

---

## TERMINAL #2 — Trigger Frontend Logging Error

### 1. Edit a frontend file

```bash
vim frontend/lib/load_profile.php
```

---

### 2. Change the request type:

```php
"type" => "get_profile",
```

to:

```php
"type" => "get_profileBAD",
```

### What this does

The frontend will now send an invalid request type to the backend.

The backend will reject the request and generate a distributed log entry.

---

### 3. Redeploy the application to Apache (`/var/www/html`)

```bash
./deploy.sh
```

### What this script does

- Copies updated project files into Apache's web directory
- Updates the live application running in `/var/www/html`

---

### 4. Trigger the error

Open the profile page while logged into the website.

---

### 5. Observe the distributed log entry

Terminal #1 should display a new log entry similar to:

```text
{
    timestamp: 2026-05-06 01:45:32,
    level: WARNING,
    source: backend,
    message: Unsupported backend request type: get_profileBAD
}
```

---


# Useful Stuff To Know

## Clear the distributed log file

```bash
sudo truncate -s 0 /var/log/guiltyspark/distributed.log
```

- `truncate`
  Used to resize a file

- `-s`
  Means "set file size"

- `0`
  Sets the file size to 0 bytes

---

## Stop watching the log file

Press:

```text
CTRL + C
```

---

# Main Distributed Logging Components
|--------------------------------------------------------------------------------|
| Component............................Purpose                                   |
|--------------------------------------------------------------------------------|
| `logClient.php`......................Sends log messages to RabbitMQ            |
| `logListener.php`....................Receives log messages from RabbitMQ       |
| `distributed.log`....................Centralized log file                      |
| `logListener.service`................Systemd service for log listener          |
| `testRabbitMQServer.service`.........Backend RabbitMQ listener service         |
| `logExchange`........................RabbitMQ fanout exchange for logs         |
| `logQueue`...........................RabbitMQ queue that receives log messages |
|--------------------------------------------------------------------------------|

---

# Log File Location

```text
/var/log/guiltyspark/distributed.log
```

---

# Systemd Service Commands

## Restart backend listener

```bash
sudo systemctl restart testRabbitMQServer.service
```

## Restart log listener

```bash
sudo systemctl restart logListener.service
```

## Check backend listener status

```bash
sudo systemctl status testRabbitMQServer.service
```

## Check log listener status

```bash
sudo systemctl status logListener.service
```