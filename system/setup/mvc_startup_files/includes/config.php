<?php require_once dirname(__FILE__) . "/../admin/includes.php";

loadView("nofolder");

if(get_option("SiteMultilanguageMode") == "multilanguage")
{
	$language = getLanguage();
	setLanguage($language);
	$i18n = listI18nByScope("global");
	setGlobal("i18n",$i18n);
}

$sitetitle = get_option("siteTitle");
$description = get_option("description");
$keywords = get_option("keywords");
$analystic = get_option("analystics");

/******************************************************************************************************/
/******************************************************************************************************/
