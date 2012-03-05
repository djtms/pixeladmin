<?php
$messageId = $_GET["messageId"];
global $MODEL;$msg = $MODEL->MESSAGE->selectMessage($messageId);if($msg->messageId == null){	postMessage("İstediğiniz mesaj kaydı bulunamadı!",true);	header("Location:admin.php?page=messages");	exit;}if($msg->readStatus == "unread")
	$MODEL->MESSAGE->setReadStatus($messageId,"read");$master->addScript("js/pages/readmessage.js");$readmessage->render();

