<?php
/*
 * Module Name: PixelPanel Documentation
 * Description: PixelPanel'in kullanımı ile ilgili anlatım ve örnekler içeren modül
 * Author: Mehmet Hazar Artuner
 * Author Url: http://www.hazarartuner.com
 * Version: 1.0
 * 
 * */


addMenu("Dökümantasyon","","Dökümantasyon","documentation_page", "",5, USER_GUEST, false);

addSubMenu("Custom Inputs", "Custom Inputs", "documentation_page", "doc_custom_inputs", dirname(__FILE__) . "/custom_inputs.php");
addPage("File Inputs", "documentation_page", "doc_file_inputs", dirname(__FILE__) . "/file_inputs.html");
addPage("Date/Time Inputs", "documentation_page", "doc_date_inputs", dirname(__FILE__) . "/date_inputs.html");




if(in_admin){
	echo "<link rel='stylesheet' href='modules/documentation/syntaxhighlighter/styles/shCore.css' />";
	echo "<link rel='stylesheet' href='modules/documentation/syntaxhighlighter/styles/shThemeDefault.css' />";
	echo "<script type='text/javascript' src='modules/documentation/syntaxhighlighter/scripts/shCore.js'></script>";
	echo "<script type='text/javascript' src='modules/documentation/syntaxhighlighter/scripts/shBrushJScript.js'></script>";
	echo "<script type='text/javascript' src='modules/documentation/syntaxhighlighter/scripts/shBrushPhp.js'></script>";
	echo "<script type='text/javascript' src='modules/documentation/syntaxhighlighter/scripts/shBrushXml.js'></script>";
	echo "<script type='text/javascript' src='modules/documentation/js/documentation.js'></script>";
}

