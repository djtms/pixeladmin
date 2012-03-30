<?php require_once dirname(__FILE__) . "/../admin/includes.php";

loadView("nofolder");

if(get_option("admin_SiteMultilanguageMode") == "multilanguage")
{
	$language = getLanguage();
	setLanguage($language);
	$i18n = listI18nByScope("global");
	setGlobal("i18n",$i18n);
}

$sitetitle = get_option("admin_siteTitle");
$description = get_option("admin_description");
$keywords = get_option("admin_keywords");
$analystic = get_option("admin_analystics");

/******************************************************************************************************/
/******************************************************************************************************/
