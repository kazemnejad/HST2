<?php
function echo_nobuffer($s) {
	echo $s;
	flush();
}

ob_end_flush();
echo str_repeat(' ', 1024)."\n";
flush();
