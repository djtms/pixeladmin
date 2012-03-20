<?php
if(in_admin)
{
	$_SESSION["i18nLanguage"] = isset($_SESSION["i18nLanguage"]) ? $_SESSION["i18nLanguage"] : "tr";
	$_SESSION["i18nLanguage"] = isset($_POST["i18nLanguage"]) ? $_POST["i18nLanguage"] : $_SESSION["i18nLanguage"];
	
	global $master;
	global $MODEL;
	
	$MODEL->I18N->language = $MODEL->LANGUAGE->getDefaultLanguage();
	$master->setGlobal("defaultLanguage", $MODEL->I18N->language);
	$master->setGlobal("availableLanguages",$MODEL->LANGUAGE->listActiveLanguages());	
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

switch($_POST["action"])
{
	case("ajaxSaveI18n"):	ajaxSaveI18n();	exit;
	case("selectI18n"):		ajaxSelectI18N(); exit;
}

function ajaxSaveI18n()
{
	global $MODEL;
	
	if(!isset($_POST["i18nLanguage"]))
		return false;
	
	$MODEL->I18N->language = $_POST["i18nLanguage"];
	$codes = $_POST["i18nCode"];
	$texts = $_POST["i18nText"];
	
	for($i = 0, $j = sizeof($codes); $i<$j; $i++)
	{
		$MODEL->I18N->setI18n($codes[$i], $texts[$i]);
	}

	return true;
}

function ajaxSelectI18N()
{
	$language = $_POST["i18nLanguage"];
	$_SESSION["i18nLanguage"] = $language;
	$codes = json_decode($_POST["codes"]);
	$response = array();
	global $MODEL;

	$MODEL->I18N->language = $language;

	foreach($codes as $c)
	{
		$response[] = array("i18nCode"=>$c->i18nCode,"text"=>$MODEL->I18N->getI18n($c->i18nCode));
	}
	echo json_encode($response);
}
	
function saveI18n()
{
	global $MODEL;
	
	if(!isset($_POST["i18nLanguage"]))
		return false;
		
	$MODEL->I18N->language = $_POST["i18nLanguage"];
	$texts = json_decode($_POST["i18nTextsGroup"]);
	
	foreach($texts as $t)
	{
		$MODEL->I18N->setI18n($t->i18nCode, $t->text);
	}
	
	return true;
}

/**********************************************************************************/

function setLanguage($language)
{
	global $MODEL;
	global $DB;
	
	$DB->execute("SET LC_TIME_NAMES=?", array($language));
	$MODEL->I18N->language = $language;
	$_SESSION["language"] = $language;
}

function getLanguage()
{
	global $MODEL;
	
	return isset($_SESSION["language"]) ? $_SESSION["language"] : $MODEL->LANGUAGE->getDefaultLanguage();
}

function getDefaultLanguage()
{
	global $MODEL;
	
	return $MODEL->LANGUAGE->getDefaultLanguage();
}

function generateLanguageLinks($targetPage = null)
{
	global $MODEL;
	
	$currentLanguage = getLanguage();
	$languages = $MODEL->LANGUAGE->listActiveLanguages();
	$targetPage = $targetPage == null ? $_SERVER["REQUEST_URI"] : $targetPage;
	$languageLinks = "";
	
	foreach($languages as $l)
	{
		$languageLinks .= '<a href="index.php?language=' . $l->locale . '&back=' . $targetPage . '" ' . ($currentLanguage == $l->locale ? ' class="selected" ' : "") . '>' . $l->name . '</a>';
	}
	
	return $languageLinks;
}

function setI18n($i18nCode, $text, $scope="")
{
	global $MODEL;
	
	return $MODEL->I18N->setI18n($i18nCode, $text, $scope);
}

function getI18n($i18nCode)
{
	global $MODEL;
	
	return $MODEL->I18N->getI18n($i18nCode);
}

function listI18nByScope($scope="global")
{
	global $MODEL;
	
	return $MODEL->I18N->listI18nByScope($scope);
}

function deleteI18n($i18nCode)
{
	global $MODEL;
	
	return $MODEL->I18N->deleteI18n($i18nCode);
}