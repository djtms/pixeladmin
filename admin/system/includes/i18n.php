<?php
if(in_admin)
{
	$_SESSION["i18nLanguage"] = isset($_SESSION["i18nLanguage"]) ? $_SESSION["i18nLanguage"] : "tr";
	$_SESSION["i18nLanguage"] = isset($_POST["i18nLanguage"]) ? $_POST["i18nLanguage"] : $_SESSION["i18nLanguage"];
	
	global $master;
	global $ADMIN;
	
	$ADMIN->I18N->language = $ADMIN->LANGUAGE->getDefaultLanguage();
	$master->setGlobal("defaultLanguage", $ADMIN->I18N->language);
	$master->setGlobal("availableLanguages",$ADMIN->LANGUAGE->listActiveLanguages());	
}
else
{
	if(isset($_GET["language"]) && ($_GET["language"] != ""))
	{
		setLanguage($_GET["language"]);
		if(isset($_GET["back"]))
		{
			header("Location:" . $_GET["back"]);
			exit;
		}
	}
}

switch($_POST["admin_action"])
{
	case("ajaxSaveI18n"):	ajaxSaveI18n();	exit;
	case("selectI18n"):		ajaxSelectI18N(); exit;
}


// seçili olan dil için belirlenmiş date_format değerini define et.
if(multilanguage_mode){
    $temp = $DB->get_row("SELECT * FROM {$DB->tables->language} WHERE locale=?", array(getLanguage()));

    define("date_format", $temp->date_format);

    if(function_exists("setGlobal")){
        setGlobal("date_format", date_format);
    }
}

function ajaxSaveI18n()
{
	global $ADMIN;
	
	if(!isset($_POST["i18nLanguage"]))
		return false;
	
	$ADMIN->I18N->language = $_POST["i18nLanguage"];
	$codes = $_POST["i18nCode"];
	$texts = $_POST["i18nText"];
	
	for($i = 0, $j = sizeof($codes); $i<$j; $i++)
	{
		$ADMIN->I18N->setI18n($codes[$i], $texts[$i]);
	}

	return true;
}

function ajaxSelectI18N()
{
	$language = $_POST["i18nLanguage"];
	$_SESSION["i18nLanguage"] = $language;
	$codes = json_decode($_POST["codes"]);
	$response = array();
	global $ADMIN;

	$ADMIN->I18N->language = $language;

	foreach($codes as $c)
	{
		$response[] = array("i18nCode"=>$c->i18nCode,"text"=>$ADMIN->I18N->getI18n($c->i18nCode));
	}
	echo json_encode($response);
}
	
function saveI18n()
{
	global $ADMIN;
	
	if(!isset($_POST["i18nLanguage"]))
		return false;
		
	$ADMIN->I18N->language = $_POST["i18nLanguage"];
	$texts = json_decode($_POST["i18nTextsGroup"]);
	
	foreach($texts as $t)
	{
		$ADMIN->I18N->setI18n($t->i18nCode, $t->text);
	}
	
	return true;
}

/**********************************************************************************/

function setLanguage($language)
{
	global $ADMIN;
	$l = $ADMIN->LANGUAGE->selectLanguage($language);
	
	$ADMIN->DB->execute("SET LC_TIME_NAMES=?", array($language));
	$ADMIN->I18N->language = $language;
	$ADMIN->LANGUAGE->date_format = "'{$l->date_format}'";
	$_SESSION["language"] = $language;
}

function getLanguage(){
	global $ADMIN;
	
	return isset($_SESSION["language"]) ? $_SESSION["language"] : $ADMIN->LANGUAGE->getDefaultLanguage();
}

function getDefaultLanguage(){
	global $ADMIN;
	
	return $ADMIN->LANGUAGE->getDefaultLanguage();
}

function generateLanguageLinks($targetPage = null, $as_array = false)
{
	global $ADMIN;
	
	$currentLanguage = getLanguage();
	$languages = $ADMIN->LANGUAGE->listActiveLanguages();
	$targetPage = $targetPage == null ? $_SERVER["REQUEST_URI"] : $targetPage;
	$languageLinks = $as_array ? array()  : "";
	
	foreach($languages as $l)
	{
		$link = "index.php?language={$l->locale}&back={$targetPage}"; 
		if($as_array)
		{
			$languageLinks[] = array("link"=>$link, "name"=>$name, "locale"=>$l->locale, "abbr"=>$l->language_abbr);
		}
		else
		{
			$languageLinks .= "<a href='{$link}'" . ($currentLanguage == $l->locale ? " class='selected' " : "") . ">" . $l->language_name . '</a>';
		}
	}
	
	return $languageLinks;
}

function setI18n($i18nCode, $text, $scope="")
{
	global $ADMIN;
	
	return $ADMIN->I18N->setI18n($i18nCode, $text, $scope);
}

function getI18n($i18nCode)
{
	global $ADMIN;
	
	return $ADMIN->I18N->getI18n($i18nCode);
}

function listI18nByScope($scope="global")
{
	global $ADMIN;
	
	return $ADMIN->I18N->listI18nByScope($scope);
}

function deleteI18n($i18nCode)
{
	global $ADMIN;
	
	return $ADMIN->I18N->deleteI18n($i18nCode);
}