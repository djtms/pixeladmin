<?php 
global $MODEL;
if($_GET["action"] == "deleteMessage")
{
	if($MODEL->MESSAGE->deleteMessage($_GET["messageId"]))	{		postMessage("Başarıyla Silinidi!");
		header("Location:$currentpage");		exit;	}
	else
		postMessage("\"Mesaj\" silinemedi!",true);
}
$msgList = $MODEL->MESSAGE->listMessages();dataGrid($msgList, "Mesajlar", "messagesList", "<%fromName%>  - <%subject%>", null, "admin.php?page=readmessage&messageId=<%messageId%>", "$currentpage&action=deleteMessage&messageId=<%messageId%>");
