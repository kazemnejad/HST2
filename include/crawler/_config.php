<?php
require_once '../config.php';
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

if (empty($GLOBALS["www_has_crawl_config"])) {
// We both know about reqire_once(), I just keep old style.
$GLOBALS["www_has_crawl_config"] = 1;

/* HTML headers and footers */
$head_file = 'tpl/elt/head.php'; // file with HTML between <head> and </head> tags
$head_html = ''; // or just define HTML here e.g. $head_html = '<link rel="stylesheet" type="text/css" href="x.css" />';
$header_file = 'tpl/top/table.php'; // file with HTML right after <body> tag
$header_html = ''; // or just define HTML here e.g. $header_html = '<h1>Search</h1><hr />';
$footer_file = 'tpl/bot/html.php'; // file with HTML right before </body> tag
$footer_html = ''; // or just define HTML here e.g. $footer_html = '<hr /><p>Copyright Blah</p>';

// *** MySQL database config. Please change these lines according your host
$s_op['mysql_host'] = "localhost";
$s_op['mysql_db'] = "phpcrawl";
$s_op['mysql_user'] = "root";
$s_op['mysql_pass'] = getConfig('db', 'pass');

//$CRAWL_ENTRY_POINT_URL = "http://".$_SERVER['HTTP_HOST']; // website to crawl MUST begin with http:// prefix
// website to crawl MUST begin with http:// prefix

$CRAWL_LOCALE = "en_US"; // read more about Locate http://php.rinet.ru/manual/en/function.setlocale.php
//$CRAWL_LOCALE = "ru_RU";

$CRAWL_MAX_DEPTH = 0;   // PHP Crawler doesn't use recursive retrieving anymore! set this to 0 to disable it
$CRAWL_PAGE_EXPIRE_DAYS = 10; // Page reindex period

// **** MISC SETTINGS ****

// disable keys while crawling (might save some time)
$CRAWL_DB_DISABLE_KEYS = false;

// skip crawling long URLs
$CRAWL_URL_MAX_LEN = 1024; // default 1024

// allow crawling these extensions possible to find in any href="" attributes (<link /> or <a>)
$CRAWL_ALLOW_EXT = array('html', 'txt', 'htm', 'xhtml', 'php', 'asp', '');

// index only first CONFIG_URL_MAX_CONTENT bytes of page content
$CRAWL_URL_MAX_CONTENT = 512 * 1024; // default 150 * 1024

// HACK. cooldown time after http request.
$CRAWL_THREAD_SLEEP_TIME = 100000; //mk_sec

// **** DATA STORAGE ***
$CRAWL_CHARS_PER_WORD = 3; // number of characters to use to represent a word
// 1 for really small site; 3 for really large site; 4 for super mega, etc.
// database size increases by 2^($CRAWL_CHARS_PER_WORD * 8)
// tables should be emptied and site re-crawled if this is changed

// **** SEARCH CONFIG ****

$CRAWL_RESULTS_PER_PAGE = 10;
// ******************************** IF SEARCH IS SLOW, MAKE THE BELOW ZERO (0) ******************************
$CRAWL_SEARCH_CONTENT_SIZE = 0; // the larger this integer, the larger the description of content for each result
$CRAWL_SEARCH_TEXT_SURROUNDING_LENGTH = 3; // words
$CRAWL_SEARCH_TEXT_BOLD_QUERY_WORD = true; // bold query words in results
$CRAWL_SEARCH_TEMPLATE = '<a href="%u" class="tx_blue">%t</a><br />%c<span class="tx_url">%u</span>';
// format: %u = url, %t = title, %d = last crawled date, %c = formatted result content
// default: $CRAWL_SEARCH_TEMPLATE = '<a href="%u" class="tx_blue">%t</a><br />%c<br /><span class="tx_url">%u</span>';
// deprecated: $CRAWL_SEARCH_TEMPLATE = '&#149; <a href="%u" class="tx_blue">%t</a> &#151; %c';

// These two variables are deprecated
$CRAWL_SEARCH_TEXT_SURROUNDING_LENGHT = 70; //chars;
$CRAWL_SEARCH_MAX_RES_WORD_COUNT = 2; // larger value produces larger search page

define('LOG_URLS', false);
define('ECHO_QUERIES', false);

// *** INIT ****
setlocale (LC_ALL, $CRAWL_LOCALE);

}
