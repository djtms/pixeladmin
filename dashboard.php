<?php	require_once 'includes.php';

registerAjaxCall("SetDisplayMode", "SiteDisplayMode");
registerAjaxCall("SetMultilanguageMode","SiteMultilanguageMode");
registerAjaxCall("SetDebugMode", "SiteDebugMode");

function SiteDisplayMode()
{
	if(set_option("SiteDisplayMode", $_POST["mode"]))
		echo "succeed"; // Değiştirme
	else
		echo "error"; // Değiştirme
}

function SiteMultilanguageMode()
{
	if(set_option("SiteMultilanguageMode", $_POST["mode"]))
		echo "succeed"; // Değiştirme
	else
		echo "error"; // Değiştirme
}

function SiteDebugMode()
{
	if(set_option("SiteDebugMode", $_POST["mode"]))
		echo "succeed"; // Değiştirme
	else
		echo "error"; // Değiştirme
}
//////////////////////////////////////////////////////////////////////////////////////
if(get_option("SiteDisplayMode") == "maintanance")
{
	$maintanenceChecked = ' checked="checked" ';
}

if(get_option("SiteMultilanguageMode") == "multilanguage")
{
	$multilanguageChecked = ' checked="checked" ';
}

if(get_option("SiteDebugMode") == "debugmode")
{
	$debugmodeChecked = ' checked="checked" ';
}
///////////////////////////////////////////////////////////////////////////////////////
global $MODEL;

$logs = $MODEL->LOG->list_logs("log",3);
$messagesList = $MODEL->MESSAGE->listMessages("all",3);

foreach($logs as $l)
{
	$logsHtml .= '<li>';
	$logsHtml .= '<span class="title">' . $l->displayname . '<span style="float:right; font-style: italic; font-size:12px;">' . $l->formattedDate . '</span></span>';
	$logsHtml .= '<p class="content">' . $l->log . '</p>';
	$logsHtml .= '</li>';
}

if(sizeof($messagesList) > 0)
{
	foreach($messagesList as $m)
	{
		$link = "admin.php?page=readmessage&messageId=$m->messageId";
		
		$messagesHtml .= '<li>';
		$messagesHtml .= '<a href="' . $link . '" class="title">' . $m->fromName . '<span style="float:right; font-style: italic; font-size:12px;">' . $m->submitTime . '</span></a>';
		$messagesHtml .= '<a href="' . $link . '" class="content">' . cropString($m->subject,50) . '</a>';
		$messagesHtml .= '</li>';
	}
}
else
{
	$messagesHtml  = '<li>';
	$messagesHtml .= '<p class="content" style="color:#fc5900;">Mesaj Bulunamadı!</p>';
	$messagesHtml .= '</li>';
}

addScript("js/pages/dashboard.js");
$dashboard->render();