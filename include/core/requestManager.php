<?php
error_reporting(E_ALL);
ini_set("display_errors", "On");

chdir("../../");

require_once 'include/config.php';
require_once 'include/core/utilFunctions.php';
require_once 'include/core/database.php';
require_once 'include/core/type.php';

class RequestManager{
	public static function LOG($s, $type = LOG_TYPE_INFO) {
		hst_log($s, 'RequestManager', $type);
	}
	
	private $db;
	private $headers;
	private $file;
	private $ch;

	public function __construct(){
		#make header default value
		$this->headers = array (
			'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv=>15.0) Gecko/20100101 Firefox/15.0.1',
			'Accept: */*',
			'Accept-Language: en-us,en;q=0.5',
			'Accept-Encoding: gzip, deflate',
			'Connection: close',
			'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
		//'X-Requested-With: XMLHttpRequest',
			'Referer: http://localhost/',
			'Pragma: no-cache',
			'Cache-Control: no-cache',
			'Expect:'
			);

			#init CURL log
			$this->file = fopen("out.txt","w+");

			#init database engine
			$this->db = new db_engine;

			#make CURL object
			$this->ch = curl_init ();
	}

	public function __destruct(){
		fclose($this->file);
	}

	public function run() {
		//global $_config;
		$result = $this->db->query("SELECT * FROM forms ");
		while ($form = mysql_fetch_array($result)){
			$result = $this->db->query("SELECT * FROM inputs WHERE `formId` = '". $form['id'] . "'");

			while ($input = mysql_fetch_array($result)){
				$inputs[] = $input;
				//if (getConfig('main', 'debugMode') == 1) echo_nobuffer('INPUT: ' . $input['name'] . '->' .$input['value'] .'<br>');
			}

			$selectQuery = $this->db->query("SELECT * FROM selects WHERE `formId` = '".$form['id']. "'");
			$selects = array();

			while ($select = mysql_fetch_array($selectQuery)){
				$selects[] = $select;
				//if (getConfig('main', 'debugMode') == 1) echo_nobuffer('SELECT: ' . $select['name'].'<br>');
			}

			//sendDefault();
			$this->makeRequest($form, $inputs, $selects);
		}
	}

	private function makeRequest($form, $inputs, $selects){
		$data = array();

		self::LOG('inputs_count ' . count($inputs) .'<br>', "Make Request", "");

		#inset inputs into data as map
		foreach ($inputs as $input){
			$data[$input['name']] = generateByType(getInputType($input['name']), _NORMUSER_);
			self::LOG('INP_NAME: ' . $input['name'] . ' -> INP_GT: ' . getInputType($input['name'], _NORMUSER_) . '<br>', "MakeRequest [input]");
		}

		#insert selects into data as map
		foreach ($selects as $select){
			$opts = explode(',', $select['options']);
			#if select tag has more than one options we select secound option else we select first options
			$data[$select['name']] = (count($opts) > 1) ? $opts[1] : $opts[0];
			self::LOG('SELECT_NAME: ' . $select['name'] . ' -> SELECT_OPTION: ' . $data[$select['name']] . '<br>');
		}

		$respnse = mysql_real_escape_string($this->sendRequest($data, $form['method'], $form['full_action']));
		$request = mysql_escape_string(http_build_query($data));
		$this->db->query('INSERT INTO `results` (`formId`, `request`, `response`, `flag`) VALUES ('. $form['id'] . ", '"  . $request . "', '" . $respnse . "' ," . _NORMUSER_ . ")" );

		foreach ($data as $name => $value){
			$data[$name] = "";
			$respnse = mysql_real_escape_string($this->sendRequest($data, $form['method'], $form['full_action']));
			$request = mysql_escape_string(http_build_query($data));
			$this->db->query('INSERT INTO `results` (`formId`, `request`, `response`, `flag`) VALUES ('. $form['id'] . ", '"  . $request . "', '" . $respnse . "' ," . _EMPTY_ . ")" );
		}

		foreach ($data as $name => $value){
			$data[$name] = generateByType(getInputType($name), _HACKED_);
			$respnse = mysql_real_escape_string($this->sendRequest($data, $form['method'], $form['full_action']));
			$request = mysql_escape_string(http_build_query($data));
			$this->db->query('INSERT INTO `results` (`formId`, `request`, `response`, `flag`) VALUES ('. $form['id'] . ", '"  . $request . "', '" . $respnse . "' ," . _HACKED_ . ")" );
		}

		#set CURL options
		//curl_setopt_array ( $ch, $curlOpts );
	}

	public function makeCURL($formAction,$customOpt = array(), $formMethod = 'get', $data = array()){
		#make http query like ?name1=value1&name2=value2...
		$data = "?".http_build_query($data);
		//self::LOG('DATA: ' . $data. '<br>');

		#init file to save curl logs
		$file = fopen ( 'out.txt', 'w+' );

		#make CURL option into map
		$curlOpts = array (
		CURLOPT_URL => $formAction . (($formMethod == 'get') ? $data : '') ,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_FRESH_CONNECT => true,
		CURLOPT_VERBOSE => 1,
		CURLOPT_TIMEOUT => 15,
		CURLOPT_ENCODING => 'gzip',
		CURLOPT_STDERR => $file,
		CURLOPT_HTTPHEADER => $this->headers,
		// CURLOPT_HEADERFUNCTION => 'readHeader',
		CURLOPT_COOKIEFILE => "cookie.coo",
		CURLOPT_COOKIEJAR => "cookie.coo"
		);

		if ($formMethod== 'post')
		$curlOpts[CURLOPT_POSTFIELDS] = $data;
		#set CURL options
		curl_setopt_array ( $this->ch, $curlOpts );
		curl_setopt_array($this->ch, $customOpt);
		return $this->ch;
	}

	public function getHeaders($url){
		$this->makeCURL($url, array(CURLOPT_HEADER => true));

		$headers = array();
		$response = curl_exec($this->ch);

		if ($response === false) {
			hst_error (curl_error ( $this->ch ), 'GetHeader');
			return false;
		}

		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

		foreach (explode("\r\n", $header_text) as $i => $line){
			if ($i === 0){
				$headers['http_code'] = explode(' ', $line);
			}else{
				list ($key, $value) = explode(': ', $line);

				$headers[$key] = $value;
			}
		}

		return $headers;
	}

	public function sendRequest($data, $formMethod, $formAction, $customOpt = array(), $returnHeaders = false){
		$this->makeCURL($formAction, $customOpt, $formMethod, $data);

		if ($returnHeaders) curl_setopt_array($this->ch, array(CURLOPT_HEADER => true));

		$response = curl_exec ( $this->ch );
		if ($response === false){
			hst_error (curl_error ( $this->ch ), 'SendRequest');
			return false;
		}
			
		if ($returnHeaders){
			$headers = array();

			$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

			foreach (explode("\r\n", $header_text) as $i => $line)
			if ($i === 0)
			$headers['http_code'] = explode(' ', $line);
			else
			{
				list ($key, $value) = explode(': ', $line);

				$headers[$key] = $value;
			}

			return array($response, $headers);
		}
			
		return $response;
	}

};
/*
 $rq2 = new RequestManager();
 echo_nobuffer(print_r($rq2->makeCURL('http://localhost/graffito/', array(CURLOPT_HEADER => true)), true));*/
$rq = new RequestManager();
$rq->run();