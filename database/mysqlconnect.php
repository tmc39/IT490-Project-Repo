#!/usr/bin/php
<?php
/*
----------------
mysqlconnect.php
----------------
Creates a MySQL connection for backend use.

Used by:
- RabbitMQ backend server
- Any component that needs database access

Returns:
- mysqli connection on success
- null on failure (error is logged)
*/

require_once(__DIR__ . '/../integration/logging/logClient.php');

function getDbConnection()
{
        // NOTE: to test locally use '127.0.0.1','testUser','12345','testdb'
        // NOTE: to test over ZeroTier use '127.0.0.1', 'test', 'testpassword', 'guiltysparkdb'

        try {
                // attempt connection
                $mydb = new mysqli('127.0.0.1','testUser','12345','testdb');

        } catch (mysqli_sql_exception $e) {

                // log exception-based failure
                sendLogMessage(
                        "Database connection failed: " . $e->getMessage(),
                        "ERROR",
                        "database"
                );

                return null;
        }

        // log non-exception connection failure
        if ($mydb->connect_error) {

                sendLogMessage(
                        "Database connection failed: " . $mydb->connect_error,
                        "ERROR",
                        "database"
                );

                return null;
        }

        // success
        return $mydb;
}
?>