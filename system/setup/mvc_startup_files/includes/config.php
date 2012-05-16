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
	$sitetitle = get_option("admin_siteTitle");
	$description = get_option("admin_description");
	$keywords = get_option("admin_keywords");
}

$analystic = get_option("admin_analystics");

/******************************************************************************************************/
/******************************************************************************************************/
