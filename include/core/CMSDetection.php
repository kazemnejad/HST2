<?php
require_once 'requestManager.php';
require_once 'simple_html_dom.php';
require_once 'JoomlaDetection.php';
require_once 'WPDetection.php';
require_once 'VBDetection.php';

abstract class CMSDetector{
	public static function LOG($s, $type = LOG_TYPE_INFO) {
		hst_log($s, 'CMSDetector', $type);
	}
	
	private static $cachedArray;
	protected $baseURL;
	protected $dirArray;
	protected $mainUrlArray;

	public function __construct($url){
		self::$cachedArray = array();
		//$this->cachePageContent($url);
		$this->baseURL = $url;
		//return $this->detect($url);
		$this->init();
	}

	protected function init(){}

	public static function detectAll($types, $url){
		$result = array();
		foreach ($types as $type){
			$className = $type."Detector";
			self::LOG($className);
			$detector = new $className($url);
			$percent = $detector->detect() * 100 . '%';
			//self::LOG($type . ': ' . $percent);
			$result[$type] = $percent;
		}
		return $result;
	}

	protected abstract function detect();

	protected static function getCachedPageContent($url){
		if (isset(self::$cachedArray[$url])){
			return self::$cachedArray[$url];
			//self::LOG('isset');
		}
		$req = new RequestManager();

		$response = $req->sendRequest(array(), 'get', $url);
		self::$cachedArray[$url] = $response;
		//self::LOG('isotset');
		return $response;
	}

	protected function checkDir(){
		$score = 0;
		//TODO age 200 bud bayad content page check she ke vaghean 404 not found dare ya na!
		$req = new RequestManager();

		$wrongPageHead = $req->getHeaders($this->baseURL . '/' . 'ddskocdssjcposdc' .str_pad(rand(0, 10000000), 10, '0', STR_PAD_LEFT) . '/');
		$wrongPageCode = $wrongPageHead['http_code'][1];

		foreach ($this->dirArray as $dir){

			$headers = $req->getHeaders($this->baseURL . '/' . $dir . '/');
			//TODO dar bazi mavaghe kari ke mikonim doros nis
			$code = $headers['http_code'];
			self::LOG($dir.': '.$code[1]);

			if ($code[1] != $wrongPageCode)
			$score += 1;
			/*switch($code[1]) {
			 case 200:
				if (!$wrongPageCode != 200)
				$score += 1;
				break;
				case 403:
				if (!$wrongPageCode != 403)
				$score += 0.9;
				break;
				}*/
		}

		//		if ($score >= 60 / 100 * count($this->dirArray))
		//			return 0.9;
		return ($score / count($this->dirArray));
	}

	protected function checkMeta($array){
		$html = new simple_html_dom();
		//self::LOG('metaEnter');
		
		$html->load(self::getCachedPageContent($this->baseURL));
		//self::LOG('chache_getetd');
		foreach ($array as $name => $content){
			//self::LOG('foreach 1');
			foreach ($html->find('meta[name=' . $name . ']') as $element){
				//if (isset($array[$element->name]) && stripos($haystack, $needle)$element->content == $array[$element->name])
				//	return 1;
				//self::LOG('foreach 2');
				$pos = stripos($element->content, $content);
				if ($pos !== false)
				return 1;
			}
		}

		return 0;
	}

	protected function checkURL(){
		$req = new RequestManager();

		$wrongPageHead = $req->getHeaders($this->baseURL . '/' . 'ddskocdssjcposdc' .str_pad(rand(0, 10000000), 10, '0', STR_PAD_LEFT) );
		$wrongPageCode = $wrongPageHead['http_code'][1];

		$score = 0;
		foreach ($this->mainUrlArray as $url){
			$headers = $req->getHeaders($this->baseURL . '/' . $url);

			//TODO dar bazi mavaghe kari ke mikonim doros nis
			$code = $headers['http_code'];
			self::LOG($url.': '.$code[1]);

			if ($code[1] != $wrongPageCode)
				$score += 1;
		}

		//		if ($score >= 60 / 100 * count($this->dirArray))
		//			return 0.9;
		return ($score / count($this->mainUrlArray));
	}

}

class Page{
	public $page;
	public $header;
}

//CMSDetector::detectAll(array('Joomla'), 'http://localhost/graffito/');
//CMSDetector::detectAll(array('Joomla' , 'WordPress' , 'vBulettin'), 'http://helli3.ir/');
