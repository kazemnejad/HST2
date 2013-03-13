<link rel="stylesheet" type="text/css" href="css/graph.css" />
<link
	rel="stylesheet" type="text/css" href="css/values.css" />
<div class="container" id="cont">
	<!-- Codrops top bar -->

	<section class="main"> <input type="radio" name="resize-graph"
		id="graph-normal" checked="checked" /> <input type="radio"
		name="paint-graph" id="graph-blue" checked="checked" /> <input
		type="radio" name="fill-graph" id="f-product1" checked="checked" /> <label
		for="f-product1">Product 1</label>

	<ul class="graph-container">
	<?php
	//echo $_GET['ehtemal'];
	
	$a = explode('%', $_GET['ehtemal']);
	
	
	for ($i = 0; $i < count($a)-1 ; $i++){
		$temp = explode('!',$a[$i]);
		$result[$temp[0]] = $temp[1] . '%';
	}
	//print_r($result);
	/*$result = array(
	"Joomla" => "25%",
	"sd" => "30%",
	"sefef" => "11%",
	);*/

	foreach ($result as $cms => $percent){
		?>
		<li><span><?php echo $cms;?> </span>
			<div class="bar-wrapper">
				<div class="bar-container">
					<div class="bar-background"></div>
					<div class="bar-inner">
					<?php echo $percent;?>
					</div>
					<div class="bar-foreground"></div>
				</div>
			</div>
		</li>
		<?php
	}
	?>
		<li>
			<ul class="graph-marker-container">
			<?php
			foreach ($result as $cms => $percent){
				echo '<li style="bottom:' . $percent . ';"><span>' . $percent . '</span></li>';
			}
			?>
			</ul>
		</li>
	</ul>

	</section>

</div>
