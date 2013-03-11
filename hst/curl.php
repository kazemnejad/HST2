<?php

chdir("..");
require_once("include/core/requestManager.php");
require_once("include/core/database.php");



$g_db = new db_engine();

$ch = curl_init ();
$rm = new RequestManager();
$rm->run();
?>