<!DOCTYPE html>
<!-- Helli security team !-->
<!-- By : Invisible !-->
<?php
require_once 'include/UI/functions.php';
require_once 'include/UI/language.php';

session_start();
if(isset($_SESSION['language']) && isset($_GET['gLan']))
	$_SESSION['language'] = $_GET['gLan'];
else if(isset($_GET['gLan']))
	$_SESSION['language'] = $_GET['gLan'];
else if(!isset($_SESSION['language']))
	$_SESSION['language'] = "en";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="css/hst.css" />
	
	
	<script type="text/javascript" src="js/lib/jquery-1.9.1.js"></script>
	<script src="js/home_page.js" ></script>
</head>	

<div class="page_top_left top_menu" >
<ul class="line_menu" >
	<li><a href='index.php'><?php doLan("Home");?></a></li>
	<li>
		<a href='history.php'><?php doLan("History")?></a>
	</li>
	<li>
		<a href='http://gorgor.ir'><?php doLan("About us")?></a>
	</li>
</ul>
<a href="?gLan=en"><img src="images/uk.png" style="float: left;" title=<?php doLan("English");?> class="imageButton"> <?php //onclick="doLanguage('en' , 'language.php')"/>?></a>
<br/>
<br/>
<a href="?gLan=fa"><img src="images/Iran.png" style="float: left; margin-top: -10px; " title=<?php doLan("Persian");?> class="imageButton"> <?php //onclick="doLanguage('fa' , 'language.php')"/>?></a>
</div>
