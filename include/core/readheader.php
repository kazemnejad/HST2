function readHeader($ch, $header) {
	global $headers;
	//echo $header;
	if (strpos($header,"HTTP/") === 0){
		$a = explode(" ",$header);
		$headers['statusNum'] = $a[1];
	}
	elseif (strpos($header,"Location:") === 0){
		$b = explode(" ",$header);
		$headers['location'] = $b[1];
	}
	return strlen($header);
}