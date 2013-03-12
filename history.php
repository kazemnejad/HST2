<?php
require_once 'template/header.php';
?>
<body>
<br/>
<br/>
<br/>
<br/>
<br/>

<div id="item" class="Htable hidden">
</div> 

<?php loading('loading1');?>

<div class="Hmenu">
<select onchange="enableMonth()" name="year" >
	<option disabled="disabled" selected="selected"><?php doLan("year")?></option>
	<option>1391</option>
</select>
<select disabled="disabled" id="month">
	<option disabled="disabled" selected="selected"><?php doLan("month")?></option>
	<option onclick="d(1)">فروردین</option>
	<option onclick="d(2)">اردیبهشت</option>
	<option onclick="d(3)">خرداد</option>
	<option onclick="d(4)">تیر</option>
	<option onclick="d(5)">مرداد</option>
	<option onclick="d(6)">شهریور</option>
	<option onclick="d(7)">مهر</option>
	<option onclick="d(8)">آبان</option>
	<option onclick="d(9)">آذر</option>
	<option onclick="d(10)">دی</option>
	<option onclick="d(11)">بهمن</option>
	<option onclick="d(12)">اسفند</option>
</select>

<?php loading('loading2')?>

<div id="mainMenu">
asghar asghar gave mane sage mane khare mane
</div>

</div>
</body>
<?php
require_once 'template/footer.php'; 
?>