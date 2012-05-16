<?php require_once dirname(__FILE__) . "/../admin/includes.php";

if(multilanguage_mode)
{
	$language = getLanguage();
	setLanguage($language);
	$i18n = listI18nByScope("global");
}
