<?php
global $MODEL;

$abbStatus = "";
$editFormAction = "Ekle";

if(isset($_GET["action"]))
{
	switch($_GET["action"])
	{
		case("deleteLanguage"):
			if($MODEL->LANGUAGE->deleteLanguage($_GET["abbreviation"]))
			{
				header("Location:$currentpage");
				exit;	
			}
		break;
		
		case("editLanguage"):
			$abbStatus = ' readonly="readonly" ';
			$lang = $MODEL->LANGUAGE->selectLanguage($_GET["abbreviation"]);
			$editFormAction = "Güncelle";
			$abbreviation = $lang->abbreviation;
			$languageName = $lang->name;
		break;
	}	
}

if(isset($_POST["action"]))
{
	switch($_POST["action"])
	{
		case("Ekle"):
			if($MODEL->LANGUAGE->createLanguage($_POST["abbreviation"], $_POST["name"]))
				postMessage("Başarıyla Eklendi");
			else
				postMessage("Hata Oluştu: " . json_encode($_POST),true);
		break;
		
		case("Kaydet"):
			$MODEL->LANGUAGE->setDefaultLanguage($_POST["activeLanguage"]);
		break;
		
		case("Güncelle"):
			if($MODEL->LANGUAGE->updateLanguage($_POST["abbreviation"],$_POST["name"]))
				header("Location:$currentpage");
		break;
	}
}

$langs = $MODEL->LANGUAGE->listLanguages();

if(sizeof($langs)>0)
{
	$defaultLanguage = $MODEL->LANGUAGE->getDefaultLanguage();
	
	foreach($langs as $l)
	{
		$deleteLink = "$currentpage&action=deleteLanguage&abbreviation=$l->abbreviation";
		$editLink = "$currentpage&action=editLanguage&abbreviation=$l->abbreviation";
		$checked =  $l->abbreviation == $defaultLanguage ? " checked " : "";
		
		$languagesHtmlList .= '<li class="item"><label class="text"><input type="radio" name="activeLanguage"  ' . $checked . ' value="' . $l->abbreviation  . '" />' . $l->abbreviation . " / " . $l->name . '</label>';
		$languagesHtmlList .= '<div class="rowEditButtonsOuter"><span class="crossBtn" deleteLink="' . $deleteLink . '"></span>';
		$languagesHtmlList .= '<a class="editBtn" href="' . $editLink . '"></a></div></li>';				
	}
}
else
	$languagesHtmlList = '<li style="color:#fc5900">Dil Bulunamadı!</li>';
	
$master->addScript("js/pages/languages.js");
$languagesHtmlList;
$languages->render();
		

