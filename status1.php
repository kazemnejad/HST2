<pre dir="rtl" style="font-size: 18px; font-family: IranianSans">
<?php
$a = file("status.txt");
unset($a[0]);
echo implode("\n", $a);
?>
</pre>