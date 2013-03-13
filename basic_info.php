<?php
	require_once 'template/header.php';
	require_once 'include/classes/class_basic_information.php';
	require_once 'include/classes/SiteInfo.php';
	require_once 'include/core/requestManager.php';
	require_once 'include/core/database.php';
	
	/*if(isset($_GET['cms'])) {
		$modules = $_GET['cms'];
		if(isset($modules[1])) {	
			$result = CMSDetector::detectAll(array('Joomla', 'WordPress'), 'http://omgubuntu.co.uk/');
			foreach ($result as $cms => $value) {
				hst_log($cms . ': ' . $value);
			}
		}
	}*/
	
	$a = parse_url($_GET['url']);
	if(!isset($a['scheme']))	
		$a = parse_url("http://".$_GET['url']);
		$hostname = explode("." , $a['host']);
		$b = $hostname[count($hostname)-2] . "." . $hostname[count($hostname)-1];
		$a = parse_url("http://".$b);
	if(isset($a['host'])) {
		$url = $a['scheme']."://".$a['host']."/";
		$n_basic = new basicInformation($url);
	}
	$rm = new RequestManager();
	$db = new db_engine();
	$db->query("INSERT INTO history (basic_url, date) VALUES ('".addslashes($url)."', '".date("y/m/d")."')");
	if ($rm->getHeaders($url) !== false)
//	if (true)
	{
?>
<body>
	<div>
		<br/>
		<br/>
		<br/>
<?php
	if (!isset($n_basic)):
?>
		Object sakhte nashod , URL na motabar
<?php
	else:
?>
		<div class="table" style="text-align: center;">
		<?php $n_basic->show_url()?>
		</div>
		<div class="table" id="basic" style="height: 600px;">
			<iframe src="startDetect.php?url=<?php echo htmlentities($url)?>" name="cms" class="table" style="float: right; width: 40%; height: 90%; "></iframe>
		<iframe src="showBasic.php?url=<?php echo htmlentities($url)?>" style="height: 90%; width: 45%;" class="table"></iframe>
		<form action="start.php">
				<input type="hidden" name="url" value="<?php echo htmlentities($url)?>"/>
				<input type="submit" class="blueButton" style="width: 100%;"/>
			</form>
		</div>
		
		
		<div class="container">
            <!-- Codrops top bar -->
          
            <section class="main">
			
                <input type="radio" name="resize-graph" id="graph-normal" checked="checked" />
                <input type="radio" name="paint-graph" id="graph-blue" checked="checked" />
                <input type="radio" name="fill-graph" id="f-product1" checked="checked" /><label for="f-product1">Product 1</label>

                <ul class="graph-container">
                    <li>
                        <span>2008</span>
                        <div class="bar-wrapper">
                            <div class="bar-container">
                                <div class="bar-background"></div>
                                <div class="bar-inner">25</div>
                                <div class="bar-foreground"></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <span>2009</span>
                        <div class="bar-wrapper">
                            <div class="bar-container">
                                <div class="bar-background"></div>
                                <div class="bar-inner">95</div>
                                <div class="bar-foreground"></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <span>2010</span>
                        <div class="bar-wrapper">
                            <div class="bar-container">
                                <div class="bar-background"></div>
                                <div class="bar-inner">75</div>
                                <div class="bar-foreground"></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <span>2011</span>
                        <div class="bar-wrapper">
                            <div class="bar-container">
                                <div class="bar-background"></div>
                                <div class="bar-inner">100</div>
                                <div class="bar-foreground"></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <span>2012</span>
                        <div class="bar-wrapper">
                            <div class="bar-container">
                                <div class="bar-background"></div>
                                <div class="bar-inner">50</div>
                                <div class="bar-foreground"></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <ul class="graph-marker-container">
                            <li style="bottom:25%;"><span>25%</span></li>
                            <li style="bottom:50%;"><span>95%</span></li>
                            <li style="bottom:75%;"><span>75%</span></li>
                            <li style="bottom:100%;"><span>100%</span></li>
                        </ul>
                    </li>
                </ul>

            </section>

        </div>
<?php
	endif;
?>
	</div>
</body>
<?php
	}
	else {
		?>
		<div class="page_mid2" style="text-align: center;">
			<img src="images/erro.png"/>
			<br/>
			<?php doLan("Sorry , unable to connect to website.")?>
			<br/>
			<p style="color: gray;" ><?php doLan("It maybe caused by internet connection problem or this website hasn't been created.")?></p>
		</div>
		<?php
	}
	require_once 'template/footer.php'; 
?>
