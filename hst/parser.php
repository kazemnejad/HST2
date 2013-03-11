<?php
chdir("..");
include_once 'include/core/simple_html_dom.php';
include_once 'include/config.php';

require_once('include/crawler/_config.php');
require_once('include/crawler/_crawler.php');

require_once("include/core/requestManager.php");
require_once("include/core/database.php");

require_once('include/core/utilFunctions.php');

set_time_limit (0);
?>
<body style="background-color:black;color:#00ff24;">
<?
$g_db = new db_engine;
$g_db->setDBname("phpcrawl");

main();

function getLastId($tableName){
	global $g_db;
	$result = $g_db->query("SELECT * FROM $tableName ORDER BY id DESC LIMIT 1") or die ("last id ".mysql_error());
	$id = 0;
	if(mysql_num_rows($result) > 0)
		$id = mysql_result($result, 0, 0);
	return ++$id;
}

function parser_LOG($s, $type = LOG_TYPE_INFO) {
	hst_log($s, 'parser', $type);
}

function parse($page, $pageId){
	$html=str_get_html($page['html']);
	global $g_db;		
//	$id = getLastId("forms");
	if ($html){
		parser_LOG(" -Finding forms... ");
		foreach ($html->find('form') as $element){
			$purl = parse_url($page['url']);
			$base_url = $purl['scheme'].'://'.$purl['host'];
			global $base_host;
			$base_host = $base_url;
			$current_URL = preg_replace("/([^\/])\/[^\/]+$/i", "\\1", $page['url']);
			$full_action = makeFullQualifiedURL($element->action,$base_url,$current_URL);
			$siteId = $page['site_id'];
			$p = $g_db->query("INSERT INTO forms ( pageId, action, method, full_action ,site_id) 
							VALUES ( $pageId, '{$element->action}', '{$element->method}', '$full_action', $siteId)", false);
			if ($p === false){
				$err = mysql_error();
				if (strpos($err,'Duplicate entry') === false)
					die ("SQL Error [forms] 3: ".mysql_error());
				continue;
			}
			
			$id = mysql_insert_id();
			$count = 0;
			echo_nobuffer(" -Finding inputs... ");
			foreach ($element->find('input') as $input){
				$count++;
				$g_db->query("INSERT INTO inputs ( type, name, value, formId) 
								VALUES ( '{$input->type}', '{$input->name}', '{$input->value}', $id)") 
				or die ("SQL Error [inputs] 1: ".mysql_error());
			}
			$sid = getLastId("inputs");
			foreach ($element->find('select') as $select){
				$count++;
				$f=0;
				$lastoption = 0;
				$options = "";
				foreach ($select->find('option') as $option){ 
						$options=$options . "," . $option->value;
						$lastoption++;
				}
				$options = substr($options,1);
				$g_db->query("INSERT INTO selects ( name, options, formId) 
								VALUES ( '{$select->name}', '$options' ,$id)") 
				or die ("SQL Error [inputs] 2: ".mysql_error());
				$sid++;
			}
			parser_LOG("Finding inputs : Done!");
			
			$p = $g_db->query("UPDATE forms set inputNum=$count WHERE id=$id") ;
		}
	}
}

function stop(){
	$file = file_get_contents("sp.txt");
	if ($file == '1')
		return true;
	return false;
}

function main(){
	file_put_contents("sp.txt","0");
	
	$file = file("config.txt");
	if ($file === false)
		$pageId = -1;
	else 
		$pageId = $file[0];
	global $g_db;
	
	while (true) {
		if (stop()) {
			parser_LOG("Stopped!");
			break;
		}
		parser_LOG("checking for new link");
		$result = $g_db->query("SELECT * FROM phpcrawler_links WHERE id>$pageId ORDER BY id LIMIT 1");
		$page = mysql_fetch_array($result);
		if ($page === false) {
			parser_LOG("no new link, waiting...");
			sleep(5);
			continue;
		}
		$pageId = $page['id'];
		parser_LOG("On Page #$pageId: " . $page['url']);
		
		parse($page, $pageId);
		file_put_contents("config.txt",$pageId);
	}
}
?>
</body>