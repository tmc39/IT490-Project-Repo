<?php
/*
---------
debug.php
---------
If needed, can be used for development debugging. Forces PHP to show errors in the browser.

require_once __DIR__ . '/lib/debug.php';
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>