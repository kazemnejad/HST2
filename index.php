<?php
require_once "template/header.php"
?>
<body onload="load()" >
<form action="basic_info.php" method="get">
	<div class="page_mid search_bar">	
		<input type="hidden" name="iyu" id="iyu" value=<?php doLan("Insert your URl !")?>/>
		<input type="text"  id="url" name="url" value="" onfocus="f(this);" onblur="b(this);" class="search_field"/>
		<input type="submit" id="submit" name="submit" value="<?php doLan("Go");?>" class="submit blueButton" />
	</div>
</form>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
</body>

<?php
require_once "template/footer.php"
?>
