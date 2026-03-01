#!/usr/bin/php
<?php
/*
---------------------------
Database Connection Helper
---------------------------
This function creates a connection to MySQL.

Used by:
- RabbitMQ server (login, session validation)
- Any backend components that need DB access

Returns:
- mysqli connection if successful
- null if connection fails
*/
function getDbConnection()
{
	// Attempt to connect to the database
	$mydb = new mysqli('127.0.0.1','testUser','12345','testdb');

	// Check for connection errors
	if ($mydb->connect_error) {
		// Return null so the caller can decide what message to send back
		return null;
	}
	// If we got here, we successfully connected to the database, so return the connection
	return $mydb;
}

/*
------------------------------
TEMPLATE PROVIDED BY PROFESSOR
------------------------------

$mydb = new mysqli('127.0.0.1','testUser','12345','testdb');

if ($mydb->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;

$query = "select * from users;";

$response = $mydb->query($query);
if ($mydb->errno != 0)
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.":error: ".$mydb->error.PHP_EOL;
	exit(0);
}
*/
?>
