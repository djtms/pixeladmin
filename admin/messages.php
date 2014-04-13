<?php 


global $ADMIN;


if($_GET["admin_action"] == "deleteMessage")
{
	if($ADMIN->MESSAGE->deleteMessage($_GET["messageId"]))
	{
		postMessage($GT->BASARIYLA_SILINDI);
		header("Location:$currentpage");
		exit;
	}
	else
		postMessage($GT->MESAJ_SILINEMEDI,true);
}


$msgList = $ADMIN->MESSAGE->listMessages();
echo dataGrid($msgList, $GT->MESAJLAR, "messagesList", "{%fromName%}  - {%subject%}", null, "admin.php?page=readmessage&messageId={%messageId%}", "$currentpage&admin_action=deleteMessage&messageId={%messageId%}");
