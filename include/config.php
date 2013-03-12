<?php
error_reporting(E_ALL);
ini_set("display_errors", "On");
//global $_config;
$_config = array();
$_config['db']['host'] = "localhost";
$_config['db']['user'] = "root";
$_config['db']['pass'] = "asdfghj";
$_config['db']['name'] = "phpcrawl";
$_config['db']['lName'] = "";
$_config['main']['host'] = "localHost";
$_config['main']['sideName'] = "mainHst";
$_config['main']['user'] = "admin";
$_config['main']['pass'] = "asdfghj";
$_config['main']['lName'] = "";
$_config['main']['debugMode'] = 1;
$_config['main']['writeQueries'] = 1;
$_config['log']['path'] = "C:/xampp/htdocs/logs";
$_config['ftp']['user'] = "ftp_root";
$_config['ftp']['pass'] = "";
$_config['ftp']['host'] = "";
$_config['ftp']['port'] = "21";
$_config['cookie']['domain'] = '';
$_config['crawler']['limitTime'] = 0;
$_config['crawler']['locale'] = 'fa_IR';
$_config['crawler']['max_depth'] = 3;
$_config['crawler']['key_disable_key'] = false;
$_config['crawler']['max_url_len'] = 1024;
$_config['crawler']['allow_ext'] = array('html', 'txt', 'htm', 'xhtml', 'php', 'asp');;
$_config['crawler']['max_url_content'] = 512*1024;
$_config['crawler']['thread_sleep_time'] = 100000;
$_config['crawler']['char_per_word'] = 3;
$_config['parser']['last_page_parsed'] = 0;
$_config['log']['SiteInfo'] = 0;

function getConfig($componet, $value){
	global $_config;
	return @$_config[$componet][$value];
}

//echo getConfig('main', 'debugMode');
