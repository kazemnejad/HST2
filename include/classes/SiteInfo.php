<?php

require_once 'include/config.php';
require_once 'include/core/database.php';
require_once 'include/core/utilFunctions.php';
require_once 'include/core/requestManager.php';
require_once 'include/core/simple_html_dom.php';

define('_WHOISSERVER_', "http://www.whois.com/whois/");

class SiteInfo{
	public static function LOG($s, $type = LOG_TYPE_INFO) {
		hst_log($s, 'SiteInfo', $type);
	}
	
	private $url;
	
	public function __construct($url) {
		$this->url = $url;
	}
		
	public function getWhois(){
		#extract main domain
		$url = parse_url($this->url);	
		$action = _WHOISSERVER_ . $url['host'];
		self::LOG('whois: -> ' . $action. '<br>');
		$html = file_get_html($action);
		$whois = $html->find('div[id=registryData]');
		return $this->parseWhois($whois[0]->innerText());
	}
	
	public function getIp(){
		$url = parse_url($this->url);
		return gethostbyname($url['host']);
	}
	
	public function parseWhois($whois){
		//self::LOG("whois: " . htmlentities($whois));
		$result = str_replace("<br><br>", "<br>", $whois);
		$temp  = "";
		while ($temp != $result){
			$temp = $result;
			$result = str_replace("<br><br>", "<br>", $result);
			
			#self::LOG("RESULT: " . htmlentities($result));
			#self::LOG("whois: " . htmlentities($whois));
		}
		
		$tempArray = explode("<br>",$result);
		
		
		
		$whoisArray = array();
		//self::LOG($tempArray[0][0]);
		
		foreach ($tempArray as $key => $value) {
			if (empty($value) || $value[0] == '%'){ 
				//self::LOG($tempArray[$i][0]);
				unset($tempArray[$key]);
				continue;
			}
						
			$line = explode(":	", $value);
			hst_error(print_r($line, true), "SiteInfo");
			$whoisArray[$line[0]] = $line[1];
			self::LOG("key: " . $line[0]);
			/*for ($j = 1; $j < count($line); $j++)
				$whoisArray[$line[0]] .= $line[$j];*/
			self::LOG("value: " . $whoisArray[$line[0]]);	
		}
		return $whoisArray;
	}
	
};



/*$req = new SiteInfo("http://linuxreview.ir/");
//echo_nobuffer(htmlentities($req->parseWhois("<br><br><br><br>adsfadsfad<br>")));
$req->getWhois();*/