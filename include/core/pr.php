<?
require_once "include/core/database.php";
require_once('include/core/utilFunctions.php');

$g_db = new db_engine();
function word_count($str,$word){
	$pos=0;
	$cw=count($word);
	for ($i=0;;$i++){
		$pos=stripos($str,$word,$pos);
		if ($pos === false)
			break;
		$pos+=$cw;
	}
	return $i;
}
function pr(){
	global $g_db;
	$words=$g_db->query("SELECT * FROM words");
	$requests=$g_db->query("SELECT * FROM results");
	while ($res = mysql_fetch_object($requests))
		while ($word = mysql_fetch_object($words))
		{
			$re = word_count($requests->response,$word->word);
			$g_db->query("INSERT INTO word_count (requestId, wordId, count) 
						  VALUES ({$res->id}, '{$word->id}', '{$input->name}', '{$re}')");
		}
}
?>