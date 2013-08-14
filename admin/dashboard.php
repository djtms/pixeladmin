<?php	require_once 'includes.php';

if(isset($_POST["admin_action"]))
{
	switch ($_POST["admin_action"])
	{
		case "SetDisplayMode": 
			echo set_option("admin_display_mode", $_POST["mode"]) ? "succeed" : "error"; // Değiştirme
		exit;
		
		case "SetMultilanguageMode": 
			echo set_option("admin_multilanguage_mode", $_POST["mode"]) ? "succeed" : "error";  // Değiştirme
		exit;
		
		case "SetDebugMode": 
			echo set_option("admin_debug_mode", $_POST["mode"]) ? "succeed" : "error";  // Değiştirme	
		exit;
	}
}

//////////////////////////////////////////////////////////////////////////////////////
$maintanenceChecked = maintanance_mode ? ' checked="checked" ' : "";
$multilanguageChecked = multilanguage_mode ? ' checked="checked" ' : "";
$debugmodeChecked = debug_mode ? ' checked="checked" ' : "";
///////////////////////////////////////////////////////////////////////////////////////

global $ADMIN;

$logs = $ADMIN->LOG->list_logs("log",3);
$messagesList = $ADMIN->MESSAGE->listMessages("all",3);

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