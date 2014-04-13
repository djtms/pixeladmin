<?php


$messageId = $_GET["messageId"];


global $ADMIN;
$msg = $ADMIN->MESSAGE->selectMessage($messageId);

if($msg->messageId == null)
{
	postMessage($GT->MESAJ_KAYDI_BULUNAMADI,true);
	header("Location:admin.php?page=messages");
	exit;
}

if($msg->readStatus == "unread")
	$ADMIN->MESSAGE->setReadStatus($messageId,"read");


$master->addScript("js/pages/readmessage.js");
$readmessage->render();

