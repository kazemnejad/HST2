<?php
require_once 'template/header.php';
?>


<br/>
<br/>
<br/>

<body>


<div class="log1">
	<a href="#" onclick="loadLog('log1')">Crawler status</a><img src="images/Button-Refresh-icon.png" class="refresh imageButton" onclick="loadLog_repeat('status1' , 1000)" title="Refresh"/><img src="images/stop.png" class="stop imageButton" onclick="stopCrawler()" title="Stop"/><img src="images/start.png" class="start imageButton" onclick="startCrawler('<?php echo $_GET['url']?>')" title="Start/Resume" id="start"/>
	<br/>
	<div id="status1" style="height: 100%; overflow: auto;"></div>
</div>


<div class="log2">
	<a href="logs/log2">log2</a><img src="images/Button-Refresh-icon.png" class="refresh"/><img src="images/stop.png" class="stop" onclick="stopLog('status1')"/>
	<br/>
	<div id="status2" style="height: 100%; overflow: auto;"></div>
</div>

<br/>

<div class="log3">
	<a href="logs/log3">log3</a><img src="images/Button-Refresh-icon.png" class="refresh"/><img src="images/stop.png" class="stop" onclick="stopLog('status1')"/>
	<br/>
	<div id="status3" style="height: 100%; overflow: auto;"></div>
</div>
</body>

<?php
require_once 'template/footer.php'; 
?>