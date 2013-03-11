<?php

define("_TEXT_", 1);
define("_PASS_", 2);
define("_DATE_", 3);
define("_USER_", 4);
define("_NUMB_", 5);
define("_MAIL_", 6);
define("_DAY_", 7);
define("_YEAR_", 8);
define("_MONTH_",9);

define("_SPECCHAR_", "/[\d-_\+=\\\\\[\]\(\)<>`~\"\.?'&%\^#!@\*$:]/");

define("_NORMUSER_", 1);
define("_HACKED_", 2);
define("_EMPTY_", 0);
/*
 global $types;
 $types = array(
 "pass", "passWord", "password", "Password", "PassWord", "p", "user", "userName", "User", "UserName", "u", "name", "Name", "family", "Family","date", "Date","day","Day","num","number", "NUMB", "Number", "call", "phone", "Call", "Phone", "mobile");

 global $types;
 $map = array(
 "pass"		=> "_PASS_",
 "passWord"	=> "_PASS_",
 "password"	=> "_PASS_",
 "Password"	=> "_PASS_",
 "PassWord"	=> "_PASS_",
 "p"			=> "_PASS_",

 "user"		=> "_USER_",
 "userName"	=> "_USER_",
 "User" 		=> "_USER_",
 "UserName" 	=> "_USER_",
 "u"			=> "_USER_",

 "name"		=> "_TEXT_",
 "Name"		=> "_TEXT_",
 "family"	=> "_TEXT_",
 "Family"	=> "_TEXT_",

 "date"		=> "_DATE_",
 "Date"		=> "_DATE_",
 "day"		=> "_DATE_",
 "Day"		=> "_DATE_",

 "num"		=>	"_NUMB_",
 "number"	=>	"_NUMB_",
 "NUMB"		=>	"_NUMB_",
 "Number"	=>	"_NUMB_",
 "call"		=>	"_NUMB_",
 "phone"		=>	"_NUMB_",
 "Call"		=>	"_NUMB_",
 "Phone"		=>	"_NUMB_",
 "mobile"	=>	"_NUMB_"
 );

 */

global $map;
$map = array(
		"pass"		=> _PASS_,
		"password"	=> _PASS_,
		"p"			=> array(_PASS_),

		"user"		=> _USER_,
		"username" 	=> _USER_,
		"u"			=> array(_USER_),

		"name"		=> _TEXT_,
		"family"	=> _TEXT_,

		"date"		=> _DATE_,
		"day"		=> _DATE_,
		"year"		=> _DATE_,
		"month"		=> _DATE_,

		"num"		=>	array(_NUMB_),
		"number"	=>	_NUMB_,
		"call"		=>	_NUMB_,
		"phone"		=>	_NUMB_,
		"mobile"	=>	_NUMB_,

		"mail"		=> _MAIL_,
		"email"		=> _MAIL_,
		"e-mail"	=> _MAIL_,
);

function getInputType ($string){

	global $map;
	$string = preg_replace(_SPECCHAR_, "", $string);

	foreach ($map as $word => $def){
		$pos = stripos($string, $word);
		if (is_array($def) === true)
		if (count($string) == count($word) && $pos !== false)
			return $def[0];
		else if ( $pos !== false )
			return $def;
	}
	return _TEXT_;
}

function generateByType ($type, $generateType, $userCount = 0){
	switch ($type){
		#for simple text type
		case _TEXT_:
			if ($generateType == _NORMUSER_)
			return "hstbugfinder";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "hackedText";
			break;
			#for passwords
		case _PASS_:
			if ($generateType == _NORMUSER_)
			return "hst_Bugfinderv2";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "hackedPass";
			break;
			#for username fields
		case _USER_:
			if ($generateType == _NORMUSER_)
			return "hstUser".$userCount+10;
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "hackedhstUser".$userCount+10;
			break;
			#for date like type
		case _DATE_:
			if ($generateType == _NORMUSER_)
			return "1990/09/09";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "2060/65/98";
			break;
		case _DAY_:
			if ($generateType == _NORMUSER_)
			return "12";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "53";
			break;
		case _MONTH_:
			if ($generateType == _NORMUSER_)
			return "08";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "36";
			break;
		case _YEAR_:
			if ($generateType == _NORMUSER_)
			return "1989";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "2101";
			break;
		case _NUMB_:
			if ($generateType == _NORMUSER_)
			return "188646";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "hackedDay";
			break;
		case _MAIL_:
			if ($generateType == _NORMUSER_)
			return "info@hstBugFinder.ir";
			else if ($generateType == _EMPTY_)
			return "";
			else if ($generateType == _HACKED_)
			return "&2info[at]57s5[dot]com" ;
			break;
		default:
			return 'hstBugFinder';
	}
}



//echo preg_replace("/[\d-_\+=\\\\\[\]\(\)<>`~\"\.?'&%\^#!@\*$:]/", "", "asas+-_=\\[]()<>~`\"'?!@#$%:^&*()__+as545sd849sds5");
