<?php
require_once 'template/header.php';

$s = "1\n".
	$url = $_GET['url'];
file_put_contents("crawler/crawl_start_command.txt", $s);
?>
<meta http-equiv="refresh" content="3; url=status.php?url=<?php echo htmlentities($url)?>"/>
<br/>
<br/>
<br/>
<br/>
<br/>

<section class="main"> <!-- the component -->
<ul class="bokeh">
	<li></li>
	<li></li>
	<li></li>
	<li></li>
</ul>
</section>

<?php
/*
$s = "1\n".
	$url = $_GET['url'];
file_put_contents("crawler/crawl_start_command.txt", $s);
  		// jaE hast ke gharare befreste be module kzm
if(true) // jaye functioni ke check mikone start shode ya na
{
?> 	
	<div class="table">
	Started <br/>
	click <a href="status.php?url=<?php echo htmlentities($url)?>">here</a> to see informations !
	</div>
<?php }?>*/
?>