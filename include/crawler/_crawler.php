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

require_once "include/core/simple_html_dom.php";
require_once('include/core/utilFunctions.php');

if (empty ( $GLOBALS ["www_has_crawler"] )) {
	if (empty ( $GLOBALS ["www_has_crawl_config"] ))
		die ( "Stop. Crawler has no config. Please include _config.php first." );
		
		// ***** CRAWLER ******
	$headers = array ();
	$GLOBALS ["www_has_crawler"] = 1;
	
	function crawler_LOG($s, $type = LOG_TYPE_INFO) {
		hst_log($s, 'crawler', $type);
	}
	
	function markOldURLsToCrawl() {
		global $CRAWL_PAGE_EXPIRE_DAYS;
		sql_query ( "UPDATE phpcrawler_links SET crawl_now = 1 WHERE TO_DAYS(NOW()) - TO_DAYS(last_crawled) > %d", $CRAWL_PAGE_EXPIRE_DAYS );
		// sql_query("DELETE FROM `words`"); // clears table of words
	}
	
	function getRemainingURLsToCrawl($site_id) {
		global $CRAWL_MAX_DEPTH;
		if ($CRAWL_MAX_DEPTH > 0)
			$count = sql_fetch_hash ( "SELECT count(*) FROM phpcrawler_links WHERE site_id = %d and crawl_now = 1 and depth < %d and url != '' ", $site_id, $CRAWL_MAX_DEPTH );
		else
			$count = sql_fetch_hash ( "SELECT count(*) FROM phpcrawler_links WHERE site_id = %d and crawl_now = 1 and url != '' ", $site_id );
		$c = $count ['count(*)'];
		crawler_LOG( "(grustc) remaining: " . $c );
		return $c;
	}
	
	// Fetch ONE url to crawl
	function getURLToCrawl($site_id) {
		global $CRAWL_MAX_DEPTH;
		if ($CRAWL_MAX_DEPTH > 0)
			$url = sql_fetch_hash ( "SELECT id,url,depth FROM phpcrawler_links WHERE site_id = %d and crawl_now = 1 and depth < %d and url != '' LIMIT 1", $site_id, $CRAWL_MAX_DEPTH );
		else
			$url = sql_fetch_hash ( "SELECT id,url,depth FROM phpcrawler_links WHERE site_id = %d and crawl_now = 1 and url != '' LIMIT 1", $site_id );
		crawler_LOG( "(gutc) url: " . $url ["url"] );
		return $url;
	}
	
	function addHeadLink($site_id, $page_URL) {
		addURLToDB ( $site_id, $page_URL, 0, "<__ROOT__>" );
	}
	
	// *** ADD TO DB
	function addURLToDB($site_id, $URL, $depth, $ref) {
		// FIXME!!! add depth verification!!!
		$link_data = sql_fetch_hash ( "SELECT id, url, last_crawled, depth FROM phpcrawler_links WHERE url = %s", $URL );
		if (empty ( $link_data ["id"] )) {
			sql_query ( "INSERT INTO phpcrawler_links (site_id, url, depth, referer) 
			VALUES (%d, %s, %d, %s)", $site_id, $URL, $depth, $ref );
			return 1;
		} else if ($link_data ["depth"] > $depth) {
			sql_query ( "UPDATE phpcrawler_links depth = %d WHERE id = %d", $depth, $link_data ["id"] );
		}
		return 0;
	}
	
	function addURLsToCrawl($site_id, $URLs_clean, $depth, $ref) {
		$counter = 0;
		foreach ( $URLs_clean as $id => $URL ) {
			$counter += addURLToDB ( $site_id, $URL, $depth, $ref );
		}
		return $counter;
	}
	
	function dropURLFromDB($link_id) {
		sql_query ( "DELETE FROM phpcrawler_links WHERE id = %d", $link_id );
	}
	
	function unsetURLFromDB($link_id) {
		sql_query ( "UPDATE phpcrawler_links SET last_crawled = NOW(), crawl_now = 2 WHERE id = %d", $link_id );
	}
	
	function readHeader($ch, $header) {
		global $headers;
		
		$break = false;
		for($num = 0; $num < strlen ( $header ); $num ++) {
			switch ($header [strlen ( $header ) - ($num + 1)]) {
				case "\n" :
				case "\r" :
					break;
				default :
					$break = true;
			}
			if ($break)
				break;
		}
		$newHeader = substr ( $header, 0, - $num );
		
		if (! empty ( $newHeader )) {
			if (strpos ( $newHeader, "HTTP/" ) === 0) {
				$a = explode ( " ", $newHeader );
				$headers ['statusNum'] = $a [1];
			} else {
				list ( $key, $value ) = explode ( ": ", $newHeader, 2 );
				$headers [$key] = $value;
			}
		}
		
		return strlen ( $header );
	}
	
	function get_content($url) {
		$ch = curl_init ();
		global $CRAWL_ALLOW_EXT, $CRAWL_ENTRY_POINT_URL;
		global $headers;
		
		crawler_LOG ( " - Checking extension..." );
		// if (in_array(strtolower(substr($url, -3)), $CRAWL_SKIP_EXT)) return
		// false;$CRAWL_SKIP_EXT = array('ico', 'css', 'xsl', 'xlt', 'bmp',
		// 'jpg', 'png', 'tif', 'pdf', 'doc', 'odt', 'zip', 'exe', 'bin', 'jar',
		// 'tar', '.gz', 'bz2', 'rpm', 'dmg', 'gif');
		$fnpath = substr ( $url, strlen ( $CRAWL_ENTRY_POINT_URL ) );
		if ($fnpath !== false) {
			$path = parse_url ( $url, PHP_URL_PATH );
			$ext = pathinfo ( $path, PATHINFO_EXTENSION );
			if (! in_array ( $ext, $CRAWL_ALLOW_EXT )) {
				crawler_LOG ( "Extension not allowed: '" . $ext . "'" );
				return false;
			}
		}
		
		crawler_LOG ( " - Downloading[" . $url . "]..." );
		
		$file = fopen ( 'curl_crawler_err.txt', 'w+' );
		
		$request_headers = array (
				'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:10.0.2) Gecko/20100101 Firefox/10.0.2',
				'Accept: */*',
				'Accept-Language: en-us,en;q=0.5',
				'Accept-Encoding: gzip, deflate',
				'Connection: closed',
				'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
				'Pragma: no-cache',
				'Cache-Control: no-cache',
				'Expect:' 
		);
		
		$opts = array (CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url, CURLOPT_HEADER => 0,
				CURLOPT_HEADERFUNCTION => 'readHeader',
				CURLOPT_COOKIEFILE => "cookie.coo",
				CURLOPT_COOKIEJAR => "cookie.coo",
				CURLOPT_STDERR => $file,
				CURLOPT_HTTPHEADER => $request_headers,
				CURLOPT_VERBOSE => true,
				CURLOPT_ENCODING => 'deflate'
		);
		
		curl_setopt_array ( $ch, $opts );
		
		$string = curl_exec ( $ch );
		if ($string === false) {
			$headers = curl_error ( $ch );
			crawler_LOG ( " Error!" );
		} else {
			crawler_LOG ( " Done!" );
		}
		curl_close ( $ch );
		return array ($string, $headers );
	}
	
	function getURLsFromPage($page, $headers, $depth = 0) {
		global $CRAWL_MAX_DEPTH;
		if (($CRAWL_MAX_DEPTH > 0) && ($depth >= $CRAWL_MAX_DEPTH)) {
			crawler_LOG ( 'parse: depth is bigger than MAX_DEPTH (' . $depth . ' >= ' . $CRAWL_MAX_DEPTH . ')' );
			return array ();
		}
		$matches = array ();
		// $URL_pattern = "/\s+href\s*=\s*[\"\']?([^\s\"\']+)[\"\'\s]+/ims";
		// preg_match_all ($URL_pattern, $page, $matches, PREG_PATTERN_ORDER);
		// return $matches[1];
		// echo nl2br(htmlentities($page));
		
		if ($page == false) {
			crawler_LOG ( 'parse: Empty page!' );
		} else {
			crawler_LOG ( 'parse: parsing page' );
			$html = str_get_html ( $page );
			if ($html === false) {
				crawler_LOG("Couldn't parse page (page size: " . strlen($page) . ")");
			}
			else {
				// var_dump($html);
				find_matches($html, $matches);
				
				$comments = array();
				foreach ( $html->find ( 'comment' ) as $element ) {
					// crawler_LOG('parse: found a.href: '.$element->href);
					$text = $element->innertext ();
					$text = substr ( $text, 4, strlen ( $text ) - 7 );
					$comments[] = $text;
				}
				if (count($comments) > 0) {
					crawler_LOG ( "parse: <font color=green>got <b>" . count($comments) . "</b> comments, parsing</font>" );
					foreach($comments as $comment) {
						$html = str_get_html ( $comment );
						find_matches($html, $matches);
					}
				}
			}
		}
		
		$redirect = getRedirectLocation ( $headers );
		if (! is_null ( $redirect )) {
			crawler_LOG ( 'parse: found redirect: ' . $headers ['Location'] );
			$matches [] = $headers ['Location'];
		}
		crawler_LOG ( 'parse: got ' . count ( $matches ) . ' urls' );
		
		return $matches;
	}
	
	function find_matches($html, &$matches) {
		foreach ( $html->find ( 'a' ) as $element ) {
			// crawler_LOG('parse: found a.href: '.$element->href);
			$matches [] = $element->href;
		}
// 		foreach ( $html->find ( 'form' ) as $element ) {
// 			// crawler_LOG('parse: found form.action: '.$element->action);
// 			$matches [] = $element->action;
// 		}
		foreach ( $html->find ( 'link' ) as $element ) {
			// crawler_LOG('parse: found link.href: '.$element->href);
			$matches [] = $element->href;
		}
		foreach ( $html->find ( 'script' ) as $element ) {
			// crawler_LOG('parse: found script.src: '.$element->src);
			$matches [] = $element->src;
		}
		foreach ( $html->find ( 'frame' ) as $element ) {
			// crawler_LOG('parse: found frame.src: '.$element->src);
			$matches [] = $element->src;
		}
		foreach ( $html->find ( 'iframe' ) as $element ) {
			// crawler_LOG('parse: found iframe.src: '.$element->src);
			$matches [] = $element->src;
		}
	}
	
	function getRedirectLocation($headers) {
		return @($headers ['Location']);
	}
	
	function makeFullQualifiedURL($URL_draft, $base_URL, $current_URL) {
		global $CRAWL_URL_MAX_LEN;
		// $URL_draft = trim($URL_draft);
		
		if (strlen ( $URL_draft ) > $CRAWL_URL_MAX_LEN) {
			crawler_LOG ( "URL too long! :-\"" );
			return false;
		}
		
		// make full qualified URL
		if (strpos ( $URL_draft, "http://" ) !== 0) {
			if ($URL_draft [0] != "/")
				$URL_draft = $current_URL . "/" . $URL_draft;
			else {
				global $base_host;
				$URL_draft = $base_host . $URL_draft;
			}
		}
		$URL_draft = str_replace ( "/./", "/", $URL_draft );
		$URL_draft = preg_replace ( "/\\/[\\/]+/i", "/", $URL_draft );
		$URL_draft = str_replace ( "http:/", "http://", $URL_draft );
		$URL_draft = str_replace ( "&amp;", "&", $URL_draft );
		
		// DROP session ID
		// $URL_draft = preg_replace("/sid=[\w\d]+/i", "", $URL_draft);
		
		$pos = strpos ( $URL_draft, '#' );
		if ($pos !== false) {
			$URL_draft = substr ( $URL_draft, 0, $pos );
		}
		
		return $URL_draft;
	}
	
	function filterURLs($URLs_draft, $base_URL, $current_URL) {
		$URLs_clean = array ();
		
		foreach ( $URLs_draft as $id => $URL ) {
			// vds($URL);
			if ($URL == '') {
				crawler_LOG ( 'removing Empty URL ' );
				continue;
			}
			if ($URL [0] == '#') {
				crawler_LOG ( 'removing URL [hash]: ' . $URL );
				continue;
			}
			$URL = makeFullQualifiedURL ( $URL, $base_URL, $current_URL );
			// var_dump($URL);
			if ($URL === false || strpos ( $URL, $base_URL ) !== 0) {
				crawler_LOG ( 'removing URL: ' . $URL );
				continue;
			}
			$URLs_clean [] = $URL;
		}
		
		$URLs_c_u = array_unique ( $URLs_clean );
		crawler_LOG ( "Duplicate removal: " . count ( $URLs_clean ) . " => " . count ( $URLs_c_u ) );
		return $URLs_c_u;
	}
	
	function getPageTitle($page) {
		preg_match ( "/<title>(.*)<\\/title>/imsU", $page, $matches );
		return @$matches [1];
	}
	
	function preparePage($content) {
		// $content = preg_replace("/<script(.*)<\/script>/imsU", "", $content);
		$content = preg_replace ( "/<!--(.*)-->/imsU", "", $content );
		// TEST: added 0.7.7: remove useless spaces
		$content = preg_replace ( "/[\\s]+/ims", " ", $content );
		$content = preg_replace ( "/<\\/?(.*)>/imsU", "", $content );
		// $content = html_entity_decode($content); done in pageWords
		return $content;
	}
	
	function checkEquals($page_content_md5) {
		$page_counter = sql_fetch ( "SELECT count(*) as cnt FROM phpcrawler_links WHERE content_md5 = %s", $page_content_md5 );
		return $page_counter;
	}
	
	function sendPageToDB($link_id, $page, $page_title, $page_content, $page_content_md5, $statusNum) {
		global $CRAWL_URL_MAX_CONTENT;
		if (strlen ( $page_content ) > $CRAWL_URL_MAX_CONTENT)
			$page_content = substr ( $page_content, 0, $CRAWL_URL_MAX_CONTENT );
			// sql_query("UPDATE phpcrawler_links SET content = %s, content_md5
		// = %s, last_crawled = NOW(), crawl_now = 2 WHERE id = %d",
		// $page_content, $page_content_md5, $link_id);
		sql_query ( "UPDATE phpcrawler_links SET status_num =%d, html = %s, content = %s, content_md5 = %s, url_title = %s, last_crawled = NOW(), crawl_now = 2 WHERE id = %d", $statusNum, $page, $page_content, $page_content_md5, $page_title, $link_id );
	}
	
	function vds($var) {
		print "<!--";
		var_dump ( $var );
		print "-->";
	}
	
	// ob_end_flush();
	global $CONNECT_TO_DB;
	if (!empty($CONNECT_TO_DB))
		$crawldb = sql_open ();
	
	// badan ezafe shode!
	function getSiteId($domain) {
		$q = "SELECT `id` FROM `sites` WHERE `domain` = '$domain'";
		$r = sql_query ( $q );
		$num = mysql_num_rows ( $r );
		switch ($num) {
			case 0 :
				sql_query ( "INSERT INTO `sites` (`domain`) VALUES ('$domain')" );
				$site_id = mysql_insert_id ();
				break;
			case 1 :
				list ( $site_id ) = mysql_fetch_row ( $r );
				break;
			default :
				die ( "FATAL ERROR: num=$num for query " . htmlentities ( $q ) );
		}
		return $site_id;
	}
}

?>