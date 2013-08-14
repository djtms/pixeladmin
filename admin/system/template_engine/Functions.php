<?php

function loadView($templateFolderName = null)
{
	global $View;
	
	$View->loadView($templateFolderName);
}

function addScript($scriptUrl, $http = false)
{
	global $View;
	$GLOBALS[$View->lastViewTemplate]->addScript($scriptUrl, $http);
}

function setGlobal($name,$value)
{
	global $View;
	if(isset($GLOBALS[$View->lastViewTemplate]))
		$GLOBALS[$View->lastViewTemplate]->setGlobal($name,$value);
}

function html($htmlFileName)
{
	$template = $GLOBALS[$htmlFileName];
	if(is_object($template))
	{
		if(get_class($template) == "Template")
			$GLOBALS[$htmlFileName]->html();
		else
			echo '<p style="color:#fc5900;">Html dosyası bulunamadı!</p>';
	}
	else
		echo '<p style="color:#fc5900;">Html dosyası bulunamadı!</p>';
}

function render($htmlFileName)
{
	$template = $GLOBALS[$htmlFileName];
	if(is_object($template))
	{
		if(get_class($template) == "Template")
			$GLOBALS[$htmlFileName]->render();
		else
			echo '<p style="color:#fc5900;">Html dosyası bulunamadı!</p>';
	}
	else
		echo '<p style="color:#fc5900;">Html dosyası bulunamadı!</p>';
}