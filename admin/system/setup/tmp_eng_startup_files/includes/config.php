<?php require_once dirname(__FILE__) . "/../admin/includes.php";

loadView("nofolder");

if(multilanguage_mode)
{
	$language = getLanguage();
	setLanguage($language);
	$i18n = listI18nByScope("global");
	setGlobal("i18n",$i18n);
	setGlobal("site_title", getI18n("admin_site_titleI18N"));
	setGlobal("description", getI18n("admin_descriptionI18N"));
	setGlobal("keywords", getI18n("admin_keywordsI18N"));
}
else
{
	// Site multilanguge_mode'da değilken tarih isimlerini default olarak türkçe ayarlaması için kullanıyoruz.
	$DB->execute("SET LC_TIME_NAMES=tr_TR");
	
	setGlobal("site_title", get_option("admin_site_title"));
	setGlobal("description", get_option("admin_description"));
	setGlobal("keywords", get_option("admin_keywords"));
}

define("site_title", $site_title);
define("description", $description);
define("keywords", $keywords);

setGlobal("multilanguage_mode", multilanguage_mode);
setGlobal("maintanance_mode", maintanance_mode);
setGlobal("site_address", site_address);
setGlobal("debug_mode", debug_mode);


$analystic = get_option("admin_analystics");
/******************************************************************************************************/
/******************************************************************************************************/
