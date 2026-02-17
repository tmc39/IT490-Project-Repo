#!/usr/bin/php
<?php

$gsdb = new mysqli('127.0.0.1','gsUser','guiltyspark123','guiltysparkdb');

if ($gsdb->errno != 0)
{
	echo "failed to connect to database: ". $gsdb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;

$query = "select * from students;";

$response = $gsdb->query($query);
if ($gsdb->errno != 0)
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.":error: ".$gsdb->error.PHP_EOL;
	exit(0);
}

?>
