<?php
if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	die('Ho! boro ba AJAX bia!');
}

require_once 'include/classes/History.php';

$n_history = new History;
$n_history->print_history($_GET['date']); 
sleep(1);
