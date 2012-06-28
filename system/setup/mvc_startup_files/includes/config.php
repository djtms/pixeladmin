<?php require_once dirname(__FILE__) . "/../admin/includes.php";

loadView("nofolder");

if(multilanguage_mode)
{
	$language = getLanguage();
	setLanguage($language);
	$i18n = listI18nByScope("global");
	setGlobal("i18n",$i18n);
	$sitetitle = getI18n("admin_siteTitleI18N");
	$description = getI18n("admin_descriptionI18N");
	$keywords = getI18n("admin_keywordsI18N");
}
else
{
	// Site multilanguge_mode'da değilken tarih isimlerini default olarak türkçe ayarlaması için kullanıyoruz.
	$DB->execute("SET LC_TIME_NAMES=tr_TR");
	
	$sitetitle = get_option("admin_siteTitle");
	$description = get_option("admin_description");
	$keywords = get_option("admin_keywords");
}

$analystic = get_option("admin_analystics");

/******************************************************************************************************/
/******************************************************************************************************/
