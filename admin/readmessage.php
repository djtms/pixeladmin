<?php


$messageId = $_GET["messageId"];


global $ADMIN;
$msg = $ADMIN->MESSAGE->selectMessage($messageId);

if($msg->messageId == null)
{
	postMessage("İstediğiniz mesaj kaydı bulunamadı!",true);
	header("Location:admin.php?page=messages");
	exit;
}

if($msg->readStatus == "unread")
	$ADMIN->MESSAGE->setReadStatus($messageId,"read");


$master->addScript("js/pages/readmessage.js");
$readmessage->render();

