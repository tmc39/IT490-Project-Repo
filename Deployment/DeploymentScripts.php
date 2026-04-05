#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
$versionName = escapeshellarg("test.1.0");
$output = shell_exec("./packaging.sh $versionName");
?>
