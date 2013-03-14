<script type="text/javascript" src="js/lib/jquery-1.9.1.js"></script>
<script src="js/home_page.js" ></script>
<link rel="stylesheet" type="text/css" href="css/hst.css" />

<?php
require_once 'include/core/utilFunctions.php';
require_once 'include/core/CMSDetection.php';


//header('Location : diagram.php');
?>
<div id="dia">
<?php
/*$result = array(
	"Joomla" => "25%",
	"WordPress" => "30%",
	"vBulletin" => "11%",
);
*/



function writeCssForChart($result){
	$file = fopen ("css/values.css" , "w+");
	$counter = 1;	
	foreach ($result as $name => $percent){
		fwrite ($file, "input#f-product1:checked ~ .graph-container > li:nth-child(" . $counter . ") .bar-inner { height: " . $percent . "; bottom: 0; }\n");
		$counter++;	
	}
	fclose($file);
}

$result = CMSDetector::detectAll(array('Joomla', 'WordPress', 'vBulettin'), $_GET['url']);
foreach ($result as $cms => $value) {
	hst_log($cms . ': ' . $value);
}

writeCssForChart($result);

$b = "'";
foreach($result as $key => $value) {
		$b .= $key . '!' . $value;
}
$b .= "'";
echo '<br><a class="blueButton" onclick="showDiagram('.$b.')">SHOW IN DIAGRAM FORMAT</a>';

?>
</div>