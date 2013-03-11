<?php

require_once 'requestManager.php';
require_once 'simple_html_dom.php';
require_once 'CMSDetection.php';

class JoomlaDetector extends CMSDetector{
	protected function init(){
		$this->dirArray = array('media', 'administrator', 'images', 'templates', 'components', 'language', 'modules', 'plugins', 'includes', 'cli', 'tmp');
	}
	
	protected function detect(){
		$percent = array();
 
		$percent['meta'] = array($this->checkMeta(array('generator' => 'joomla')), 4);
		self::LOG('META: '.$percent['meta'][0]);
		$percent['dir'] = array($this->checkDir(), 3);
		self::LOG('DIR: '.$percent['dir'][0]);
		
		$sum = 0;
		$weights = 0;
		foreach ($percent as $value) {
			$sum += $value[0]* $value[1];
			$weights += $value[1];
		}
		return $sum/$weights;
	}
}

/*$head = new RequestManager();
hst_log(print_r($head->getHeaders('http://linuxreview.ir/adsfasdf.php'), true));*/