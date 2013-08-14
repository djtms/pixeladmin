<?php 


global $ADMIN;


if($_GET["admin_action"] == "deleteMessage")
{
	if($ADMIN->MESSAGE->deleteMessage($_GET["messageId"]))
	{
		postMessage("Başarıyla Silinidi!");
		header("Location:$currentpage");
		exit;
	}
	else
		postMessage("\"Mesaj\" silinemedi!",true);
}


$msgList = $ADMIN->MESSAGE->listMessages();
echo dataGrid($msgList, "Mesajlar", "messagesList", "{%fromName%}  - {%subject%}", null, "admin.php?page=readmessage&messageId={%messageId%}", "$currentpage&admin_action=deleteMessage&messageId={%messageId%}");
