<?php

function parseCustomHtml($html_string)
{
	// input[type=i18n]  elementlerini düzenle
	$html_string = preg_replace_callback("/<input[^>]*?type=(\"i18n\"|'i18n')[^>]*>/i", "parseI18nInputs", $html_string);
	
	// textarea[type=i18n]  elementlerini düzenle
	$html_string = preg_replace_callback("/<textarea[^>]*?type=(\"i18n\"|'i18n')[^>]*>/i", "parseI18nInputs", $html_string);
	
	// input[type=file] elementlerini düzenle
	$html_string = preg_replace_callback("/<input[^>]*type=(\"file\"|'file')[^>]*>/i", "parseFileInputs", $html_string);
	
	return $html_string;
}

function parseI18nInputs($match)
{	
	return preg_replace_callback("/(?:value=(\"|')(?P<i18n_code>.*?)\\1)|(?P<no_i18n_code>\/>|\>|>)/i", "defaultParser", $match[0], 1);
}

function parseI18nTextareas($match)
{
	return preg_replace_callback("/(?:value=(\"|')(?P<i18n_code>.*?)\\1)|(?P<no_i18n_code>\/>|\>|>)/i", "defaultParser", $match[0], 1);
}

function parseFileInputs($match)
{
	return preg_replace_callback("/(?:value=(\"|')(?P<file_id>.*?)\\1)/i", "defaultParser", $match[0], 1);
}

function defaultParser($match)
{
	if(isset($match["no_i18n_code"]))
	{
		return " i18n='" . uniqid() . "' />";
	}
	else if(isset($match["i18n_code"]))
	{
		$code = strlen(trim($match["i18n_code"])) > 0 ? $match["i18n_code"] : uniqid();
		return " i18n='" . $match["i18n_code"] . "' ";
	}
	else if(isset($match["file_id"]))
	{
		return " fileid='" . $match["file_id"] . "' ";
	}
}