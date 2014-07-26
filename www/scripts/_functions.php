<?php

//All the commonly used functions go here
//Most of them just divide a number
//Also, bcmath is php's built in math library that does math using string manipulation with any sized number with as many decimals as you want
//And, no floating point error
//One of my favorite reasons to use PHP
//And before you say javascript has one too
//It does, but it sucks, and it's not built in, and it even has a disclaimer saying "This could be buggy"
//But, I do use it client side all the time, so people can preview their order
//Anyway
//bcmath wins any day
//http://php.net/manual/en/ref.bc.php
//I, and many, many others, ♥ bcmath



function convertto8($str){
	$str = xpnd($str);
	return bcdiv($str,"100000000",8);
}


function convertto8w11($str){
	$str = xpnd($str);
	return bcdiv($str,"100000000",11);
}

function xpnd($str){
	//converts scientific notation to number, if there is scientific notation
	$str = strval($str);
	$str = strtolower($str);
	$str = str_replace("+", "", $str);
	if(strpos($str,"e") === false){
		return $str;
	}
	$esplit = explode("e",$str);
	$part1 =  $esplit[0];
	$zerostoadd = bcsub($esplit[1],""+strlen(explode(".",$part1)[1]));
	$part2 = str_repeat("0", $zerostoadd);
	$part1 = str_replace(".", "", $part1);
	return $part1.$part2;
}

function cutto11($str){
    $str = xpnd($str);
	return bcdiv($str,"1",11);
}

function convertto3($str){
	$str = xpnd($str);
	return bcdiv($str,"1000",3);
}

function convertto19cutto8($int){
	$str = xpnd($int);
	$zeros = "1" . str_repeat("0", 19);
	return bcdiv($str,$zeros,8);
}

function convertto11($int){
	$str = xpnd($int);
	return bcdiv($str,"100000000000",11);
}