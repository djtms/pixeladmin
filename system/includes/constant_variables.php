<?php
define("USER_GUEST",1); // User tipleri
define("USER_AUTHOR",5);
define("USER_ADMIN",10);
define("USER_SUPER",100);

$pa_menu_array = array(); // Menü özelliklerinin tutulduğu global değişken
$register_module_function; // Herhangi bir Modülün aktivastonu esnasında çalıştırılması istenen fonsiyonu tutan değişken

$admin_version = "BETA 0.6.5";
$track_wait_limit = 300; // Kullanıcı için açılan track'in kullanıcı browser'ı kapattığında veya internet bağlantısı kesildiğinde ne kadar süre daha açık kalacağını belirtir
$currenturl = $_SERVER["REQUEST_URI"];
$currentpage = "admin.php?page=" . $_GET["page"];
$default_menu_icon = "view/images/icons/default_icon.png";
$allowed_dirs_in_maintanance_mode = array("mobile"); // "Maintanance Mode" dayken erişime izin verilecek dizinlerin listesi

$common_admin_site = ""; // Admin in tek yerden kontrol edilmesi istendiğinde ortak olarak belirlenecek içinde admin panel'in olduğu sitenin adresi
$upload_root_folder = "upload"; // Upload dosyalarının bulunduğu kök dizinin adı
$system_folder = "system"; // Sistem tarafından kullanılan dosyaların bulunduğu upload dizini içindeki klasörün adı
$files_folder = "files"; // Upload edilen dosyaların bulunduğu upload dizinindeki klasörün adı
$thumbs_folder = "thumbs"; // Thumbnail lerin bulunduğu upload dizinindeki klasörün adı
$allowedFileFormatsForUpload = array("jpeg","jpg","gif","png","css","mp4","avi","flv","f4v","swf"); // Upload işleminde izin verilen dosya formatları dizisi

$upload_root_deep_url = getDeepUrl($upload_root_folder); // Bulunulan dizinden upload kök dizinine gitmek için kullanılan url

$systemurl =  $upload_root_deep_url . "$system_folder/"; // Upload edilen sistem dosyalarının bulunduğu klasörün url'si
$uploadurl =  $upload_root_deep_url . "$files_folder/";  // Upload edilen dosyaların bulunduğu klasörün url'si
$thumbsurl =  $upload_root_deep_url . "$thumbs_folder/"; // Upload edilen thumbnaillerin bulunduğu klasörün url'si

$trCharsForRegExp = preg_quote("ıİüÜöÖğĞşŞçÇ","/");

$modules_main_file_name = "main.php"; // Modüllerin başlangıç dosyasının adı
$modulesContent = ""; // module dosyaları require edildiğinde mvc dışında ekrana yazılan verileri output buffering sonucu tutan değişken.

$user_types = array("Konuk"=>USER_GUEST,"Yazar"=>USER_AUTHOR,"Yönetici"=>USER_ADMIN);

/* Hem javascript hemde html içinde kullanılabilecek şekilde tanımla */
if(in_admin)
{
	foreach($allowedFileFormatsForUpload as $format)
	{
		$allowedFileExtensionsString .= "*.{$format};";
	}

	$master->setGlobal("allowedFileExtensionsString",$allowedFileExtensionsString);
	$master->setGlobal("currentpage",$currentpage);
	$master->setGlobal("uploadurl",$uploadurl);
	$master->setGlobal("thumbsurl",$thumbsurl);
	$master->setGlobal("systemurl",$systemurl);
}