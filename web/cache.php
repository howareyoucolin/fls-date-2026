<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set("America/New_York");

// Empty cache
$files = glob(dirname(__FILE__).'/cache/*');
foreach($files as $file){
	if(is_file($file)) {
		unlink($file);
		echo '<br/>';
		echo 'Deleted ' . $file;
	}
}

echo '<br/>';

// Create home cache
$homeContent = file_get_contents("https://www.flushingdating.com/");
$homeCache = fopen("cache/home.cache", "w") or die("Unable to open file!");
fwrite($homeCache, $homeContent);
fclose($homeCache);
echo '<br/>';
echo 'Created home.cache';

// Create members cache
$membersContent = file_get_contents("https://www.flushingdating.com/members");
$membersCache = fopen("cache/members.cache", "w") or die("Unable to open file!");
fwrite($membersCache, $membersContent);
fclose($membersCache);
echo '<br/>';
echo 'Created members.cache';

echo '<br/>';

echo '<br/>';
echo 'Done at ' . date("Y-m-d h:i:sa");