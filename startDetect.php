<?php
require_once 'include/core/utilFunctions.php';
require_once 'include/core/CMSDetection.php';

$result = CMSDetector::detectAll(array('Joomla', 'WordPress', 'vBulettin'), $_GET['url']);
foreach ($result as $cms => $value) {
	hst_log($cms . ': ' . $value);
}
