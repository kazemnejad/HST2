<?php
require_once 'include/config.php';
require_once 'include/core/no_buffer.php';

define("LOG_TYPE_WARNING", "yellow");
define("LOG_TYPE_INFO", "green");

function hst_log($string, $component = NULL, $type = NULL){
	$log = getConfig('log', $component);
	if (is_null($log)) {
		if (getConfig('main', 'debugMode') != 1)
			return;
	}
	else {
		if ($log != 1)
			return;
	}
	$component = (is_null($component) ? "" : "<b>&lt;$component&gt;</b>");
	echo_nobuffer('<font color='. $type . '>'.$component.' ' . $string . "</font><br>\n");
}

function hst_error($string, $component = NULL){
	hst_log('<font color="red"><b>'. (is_null($component)? "" : ($component . ' ')) . 'ERROR: <pre>' . $string . '</pre></b></font>');
}
