<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE); // Setup esnasındaki "Notice" hatalarını gizle.

/* DATABASE *****************************************/
$dbname = '{%dbname%}';
$dbuser = '{%dbuser%}';
$dbpass = '{%dbpass%}';
$dbhost = '{%dbhost%}';
$dbcharset = 'utf8';
$timezone = '+02:00';
$prefix = "{%prefix%}";

require_once "system/classes/DB.php";
/****************************************************/

require_once "system/includes/options.php";

if(get_option("admin_SiteDebugMode") == "debugmode")
{
	define("debug_mode", true);
	ini_set("display_startup_errors", true);
	error_reporting(E_ALL ^ E_NOTICE);
	$add_modules_menu = true; // Menüye modules sayfasının eklenip eklenmemesini belirler
}
else
{
	define("debug_mode", false);
	ini_set("display_startup_errors", false);
	error_reporting(0);
	$add_modules_menu = false; // Menüye modules sayfasının eklenip eklenmemesini belirler
}

/*
* Web sitesinin çalıştığı ana dizin dosyasının adını hesapla. Bunu php'nin getcwd() fonksiyonu ile
* yapmıyoruz çünkü web sitemiz ana dizinde değilde başka alt dizinlerden birinde çalıştırılıyor olabilir
* halbuki bize bu sistemin çalıştığı ana dizin dosyasının adı lazım.
*/
$working_folder_name = preg_quote(basename(dirname(dirname(__FILE__))), "/");


define("working_folder_name", $working_folder_name);
define("multilanguage_mode", get_option("admin_SiteMultilanguageMode") == "multilanguage" ? true : false);
define("maintanance_mode", get_option("admin_SiteDisplayMode") == "maintanance" ? true : false);


// View engine'i bağla
require_once dirname(__FILE__) . "/system/view_engine/View.php";

if(in_admin)
{
	loadView("nofolder");
	setGlobal("debug_mode", debug_mode);
	setGlobal("multilanguage_mode", multilanguage_mode);
	setGlobal("maintanance_mode", maintanance_mode);
}

/* SECURITY */
$secureKey = '{%securekey%}';
$sessionKeysPrefix = "{%sessionKeysPrefix%}";
/***************************************************/