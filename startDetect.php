<?php
require_once 'include/core/utilFunctions.php';
require_once 'include/core/CMSDetection.php';

function writeCssForChart($result){
	$file = fopen ("values.css" , "w+");
	$counter = 1;
	
	foreach ($result as $name => $percent){
		fwrite ($file, "input#f-product1:checked ~ .graph-container > li:nth-child(" . $counter . ") .bar-inner { height: " . $percent . "; bottom: 0; }");
		$counter++;	
	}
	fclose($file);
}

$result = CMSDetector::detectAll(array('Joomla', 'WordPress', 'vBulettin'), $_GET['url']);
foreach ($result as $cms => $value) {
	hst_log($cms . ': ' . $value);
}

writeCssForChart($result);

echo '<input type="button" value="salam!" onclick="addDiagram" />';