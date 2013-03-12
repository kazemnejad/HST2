<?php 
/**
 * <h1>PHP Crawler</h1>
 * @author Vladimir Fedorkov, Doug Martin, and Sumit Dutta
 * @version 0.8 2007-05-04
 * @link http://astellar.com/
 * @copyright 2005-2007
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

chdir("..");
require_once('include/crawler/_config.php');
require_once('include/crawler/_db.php');
require_once('include/crawler/_crawler.php');
require_once('include/core/utilFunctions.php');

set_time_limit (0);
// error_reporting (E_ERROR | E_WARNING | E_PARSE);
error_reporting (E_ALL);

$CRAWL_ENTRY_POINT_URL = $_GET['kerm'];

$crawl_max_shown_depth = $CRAWL_MAX_DEPTH - 1;

crawler_LOG("PHP-Crawler started...");
crawler_LOG("log format: Crawling: [Current depth ({$crawl_max_shown_depth} MAX)] URL Action");
$site_id = getSiteId($CRAWL_ENTRY_POINT_URL);
if ($CRAWL_DB_DISABLE_KEYS) sql_query("/*!40000 ALTER TABLE `phpcrawler_links` DISABLE KEYS */;");
addHeadLink($site_id, $CRAWL_ENTRY_POINT_URL);

markOldURLsToCrawl();

$base_host = parse_url($CRAWL_ENTRY_POINT_URL);
$base_host = $base_host['scheme'].'://'.$base_host['host'];

function stopped(){
	$file = file("sc.txt");
	if ($file[0] == '0')
		return false;
	return true;
}

file_put_contents("sc.txt","0");

class Status {
	public $current_url = null, $crawled_pages = 0, $num = 0, $msg = null, $parsedNum = 0;
	
	function __construct() {
		$this->load_data();
		//$this->save_data("Initializing"); vase in ke khit shi
	}
	
	/**
	 * @param field_type $current_url
	 */
	public function setCurrent_url($current_url, $save = true) {
		$this->current_url = $current_url;
		if ($save)
			$this->save();
		return $this;
	}

	/**
	 * @param field_type $crawled_pages
	 */
	public function setCrawled_pages($crawled_pages, $save = true) {
		$this->crawled_pages = $crawled_pages;
		if ($save)
			$this->save();
		return $this;
	}

	/**
	 * @param field_type $num
	 */
	public function setNum($num, $save = true) {
		$this->num = $num;
		if ($save)
			$this->save();
		return $this;
	}

	/**
	 * @param field_type $msg
	 */
	public function setMsg($msg, $save = true) {
		$this->msg = $msg;
		if ($save)
			$this->save();
		return $this;
	}
	
	public function setParsedNum($parsedNum, $save = true) {
		$this->parsedNum = $parsedNum;
		if ($save)
			$this->save();
		return $this;
	}
	
	function save() {
		$this->save_data($this);
	}
	
	function load_data() {
		$s = file('status.txt');
		global $CRAWL_ENTRY_POINT_URL;
		if($CRAWL_ENTRY_POINT_URL != trim($s[0])) {
			$this->setMsg("در حال شروع به کار");
			return;
		}
		unset($s[0]);
		$this->setMsg("در حال بازیابی اطلاعات(اگه اینو دیدی با این شماره تماس بگیر ۰۹۱۲۲۵۹۷۴۱۸)");
		foreach($s as $l) {
			$a = explode(": ", trim($l) , 2);
			switch($a[0]) {
			case "وضعیت فعلی":
				//$this->msg = $a[1];
				break;
			case "صفحه ی فعلی":
				$this->current_url = $a[1];
				break;
			case "تعداد لینک های بررسی شده":
				$this->parsedNum = $a[1];
				break;
			case "تعداد صفحات ثبت شده":
				$this->crawled_pages = $a[1];
				break;
			case "صفحات در صف انتظار":
				$this->num = $a[1];
				break;
			}
		}
	}
	
	function save_data($data) {
		file_put_contents('status.txt', $data);
	}
	
// 	function finish() {
// 		update()
// 	}
	
	function __toString() {
		global $CRAWL_ENTRY_POINT_URL;
		$s = $CRAWL_ENTRY_POINT_URL."\n";
		if (!is_null($this->msg))
			$s .= "وضعیت فعلی: ".$this->msg . "\n";
		if (!is_null($this->current_url))
			$s .= "صفحه ی فعلی: ". $this->current_url . "\n";
		if (!is_null($this->parsedNum))
			$s .= "تعداد لینک های بررسی شده: ". $this->parsedNum . "\n";
		if (!is_null($this->crawled_pages))
			$s .= "تعداد صفحات ثبت شده: ". $this->crawled_pages . "\n";
		if (!is_null($this->num))
			$s .= "صفحات در صف انتظار: " . $this->num . "\n";
		
		return $s;
	}
}

$dud = fopen("crawler.txt","w+");
$st = new Status();
$st->setMsg("شروع");

$url_counter = $st->crawled_pages;
$parse_counter = $st->parsedNum;
$url_size = 0;

while(true) {
	if (stopped()) {
		$st->setMsg("متوقف شد!");
 		crawler_LOG("Stopped!");
		break;
	}
	$st
		->setNum(getRemainingURLsToCrawl($site_id), false)
		->setMsg("خواندن لینک بعدی");
	$URL_info = getURLToCrawl($site_id);
 	if ($URL_info === false) {
 		crawler_LOG("Finished!");
 		$st->setMsg('پایان!');
 		break;
 	}
	if (LOG_URLS)
	   crawler_LOG("Got page: ".var_export($URL_info, true));
   
   $url_counter++;
   $st->setCrawled_pages($url_counter);
   $URL = $URL_info["url"];
   $st->setCurrent_url($URL);
   $current_URL = preg_replace("/([^\\/])\\/[^\\/]+$/i", "\\1", $URL_info["url"]);
   
	// Cooldown
   usleep ($CRAWL_THREAD_SLEEP_TIME);
   
   crawler_LOG("Crawling: [" . $URL_info["depth"] . "] {$URL} (".$current_URL.")");
   fwrite($dud,'Crawling: [' . $URL_info["depth"] . '] '. $URL . "\n");
   $st->setMsg("در حال دانلود");
   list($page, $headers) = get_content($URL);
   if ($page === false) {
   	$error = $headers;
   	dropURLFromDB($URL_info["id"]);
   	crawler_LOG(" - FAILED/REMOVED: <font color=red>".$error."</font>");
   	fwrite($dud," - FAILED/REMOVED: ".$error . "\n");
   	continue;
   }
   
   $page_size = strlen($page);
   $url_size += $page_size;
   crawler_LOG(" " . ($page_size / 1024) . "kb");
   $st->setMsg("در حال تحلیل");
//    $page_content = preparePage($page);
   $page_content = $page;
//    crawler_LOG("Page content: ".htmlentities($page_content));
   $page_content_md5 = md5($page_content);
   
//   $page_hash = prepareHash($page_content); // puts words into DB; returns number of words
//   $page_hash_md5 = md5($page_hash);
   
   $location = getRedirectLocation($headers);
   if (is_null($location)) {
	   $page_counter = checkEquals($page_content_md5);
	   if($page_counter != false) {
	      unsetURLFromDB($URL_info["id"]);
	      crawler_LOG(" - SKIPPED ({$page_counter} equals)");
	      continue;
	   }
   }
   $st->setMsg("در حال استخراج لینک های صفحه");
   $URLs_draft = getURLsFromPage($page, $headers, $URL_info["depth"] + 1); //array
   $parse_counter += count($URLs_draft);
   $st->setParsedNum($parse_counter);
//   crawler_LOG('URLs_draft: ');
//	var_dump($URLs_draft);
   $page_title = getPageTitle($page);
//   crawler_LOG('URLs_title: ');
//  var_dump($URLs_title);
   $URLs_clean = filterURLs($URLs_draft, $CRAWL_ENTRY_POINT_URL, $current_URL); //$base_URL, $current_URL
//   crawler_LOG('URLs_clean: ');
//   var_dump($URLs_clean);
   $st->setMsg("در حال ثبت لینک های استخراج شده");
   $URLs_to_crawl = addURLsToCrawl($site_id, $URLs_clean, $URL_info["depth"] + 1, $URL);
//   crawler_LOG('URLs_to_crawl: ');
//   var_dump($URLs_to_crawl);
   
   crawler_LOG(" +" . $URLs_to_crawl . " urls.<br/>");
   fwrite($dud," +" . $URLs_to_crawl . " urls". "\n");
   $st->setMsg("در حال ذخیره صفحه");
   sendpagetoDB($URL_info["id"], $page, $page_title, $page_content, $page_content_md5,$headers['statusNum']);
   //sendPageToDB($URL_info["id"], $page_title, $page_hash, $page_hash_md5);
}

if ($CRAWL_DB_DISABLE_KEYS) sql_query("/*!40000 ALTER TABLE `phpcrawler_links` ENABLE KEYS */;");
	
	crawler_LOG($url_counter . " pages crawled, " . ($url_size/1000) . "Kb processed");
	fwrite($dud,$url_counter . ' pages crawled, ' . ($url_size/1000) . 'Kb processed' . "\n");
?>