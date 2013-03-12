<?php
class basicInformation
{
	private $url;
	
	function __construct($url) {
		$this->url = $url;
	}
	
	function show() {
?>
<table class="basic_info" border="1" width="100%">
<tr ><th>info</th><th>value</th></tr>
<?php
		$req = new SiteInfo($this->url);
		$a = $req->getWhois();
		foreach ($a as $key => $value) {
			if ($key != 'e-mail')
				$value = htmlentities($value);
			else {
				$html = str_get_html($value);
				$imgs = $html->find('img');
				$img = $imgs[0];
				$img->src = 'http://www.whois.com'.$img->src;
				$value = $html->__toString();
			}
?>
<tr><td><?= htmlentities($key) ?></td><td><?= $value ?></td></tr>
<?php
		}
?>
</table>
<?php
	}
	function show_url()
	{
		echo doLan("Your URL: ").$this->url;
	}
};
?>