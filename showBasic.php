<?php
require_once 'include/classes/class_basic_information.php';
require_once 'include/classes/SiteInfo.php';

$n_basic = new basicInformation($_GET['url']);
?>
<link rel="stylesheet" type="text/css" href="css/hst.css" />
<div class="table" id="basic">
<?php
$n_basic->show();
?>
</div>