//var input_default_value = 'Insert your URL !';
var input_default_value = "lol";

function load() {
	input_default_value = document.getElementById('iyu').value;
	b(document.getElementById('url'));
	light_style(document.getElementById('url'));
}

function light_style(e) {
	e.style.color = 'blue';
}

function dark_style(e) {
	e.style.color = 'black';
}

function b(e) {
	if (e.value == '') {
		e.value = input_default_value;
		light_style(e);
	}
}

function f(e) {
	if (e.value == input_default_value)
		e.value = '';
	dark_style(e);
}

function t(n) {
	$('#item').hide();
	$('#loading1').show();
	$.get("history_item.php", {
		date: n,
	}).done(function(data) {
		$('#item').html(data).show();
		$('#loading1').hide();
	}).fail(function(jqXHR, textStatus, errorThrown) {
		alert("ERROR! " + errorThrown);
	});
}

function d(n)
{
	$('#mainMenu').hide();
	$('#loading2').show();
	$.get("days.php", {
		date: n,
	}).done(function(data) {
		$('#mainMenu').html(data).show();
		$('#loading2').hide();
	}).fail(function(jqXHR, textStatus, errorThrown) {
		alert("ERROR! " + errorThrown);
	});
}
function enableMonth()
{
	enableAttr('month');
}
function enableAttr(id)
{
	if(typeof(id) == 'string')
		$element = $('#' + id);
	else
		$element = $(id);
	$element.removeAttr('disabled');
}
function toggle_element(id)
{
	if(typeof(id) == 'string')
		$element = $('#' + id);
	else
		$element = $(id);
	$element.toggle(500);
}

function loadLog_repeat(id, interval) {
	var $element;
	if(typeof(id) == 'string')
		$element = $('#' + id);
	else
		$element = $(id);
	clearTimeout($element.attr('refreshTimeout'));
	$.get(id + ".php")
	.done(function(data) {
		if(data[20] != "")
			$element.html(data).show();
		var t = setTimeout(function() {
			loadLog_repeat(id, interval);
		}, interval);
		$element.attr('refreshTimeout', t);
	}).fail(function(jqXHR, textStatus, errorThrown) {
		alert("ERROR! " + errorThrown);
	});
}

function stopCrawler() {
	$.get("hst/stopc.php");
}

function startCrawler(url) {
	$.get("hst/crawler.php", {
		kerm:url,
	}).done(function(data) {
//		alert("finished");
//		alert(data);
		setTimeout(function(){
			text = $('#status1 pre').html();
			if(text !== undefined) {
				if(text[12] == "پ" || text[12] == "م")
					return;
			}
			startCrawler(url);
		} , 2000);
	});
}

function doLanguage(d , a) {
	$.get(a , {
		zz: d
		
	});
	//$.cookie('language', d , { expires: 365, path: '/' });
	//$session('language' , d);
	
}

function submitt() {
	alert("asghar");
}

function showDiagram(a) {
	$.get('diagram.php' , {
		ehtemal: a
	}).done(function(data){
		$('#dia').html(data).show();
	});
}

$(function(){
	$('img.refresh').click();
	$('img.start').click();
});

