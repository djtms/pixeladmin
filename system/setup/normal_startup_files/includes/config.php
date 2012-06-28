<?php require_once dirname(__FILE__) . "/../admin/includes.php";

if(multilanguage_mode)
{
	$language = getLanguage();
	setLanguage($language);
	$i18n = listI18nByScope("global");
}
else
{
	// Site multilanguge_mode'da değilken tarih isimlerini default olarak türkçe ayarlaması için kullanıyoruz.
	$DB->execute("SET LC_TIME_NAMES=tr_TR");
}
