<?
	include 'simple_html_dom.php';
	$html = file_get_html('ServiceLogin.htm');
	$post_field = array();
	$request = new HttpRequest('https://accounts.google.com/ServiceLoginAuth',HttpRequest::METH_POST);
	foreach($html->find('form') as $element){
		//echo $element->action . ' : ' . $element->method . '<br>' ; 
		foreach($element->find('input') as $in){
			if (1/*($in->type !="hidden"*/){
				$post_field[$in->name] = $in->value;
				//echo $in->name . '  -  ';
				//echo $in->type . '  :  ' .$in->value . '<br>';
			}
		}
	}
	//$post_field['dnConn'] = 1;
	//$post_field['checkConnection'] = 1;
	
	$post_field['Email'] = "a.k.kartoosh";
	$post_field['Passwd'] = "123654789";
	$request->addPostFields($post_field);
	try{
		echo $request->send()->getBody();
	}catch(HttpRequest $ex){
		echo $ex;
	}
	
	echo "--------------------------------------------<br>";
	/*foreach(array_keys($post_field) as $key){
		if ($post_field[$key] == NULL)
			echo $key . "<br>";
	}*/
	/*
	 * dnConn
	checkConnection
	timeStmp
	secTok
	Email
	Passwd
	*/
?>
