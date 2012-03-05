<?php require_once 'includes.php'; 

global $MODEL;
$user_id = $_GET["user"];
$ticket_key = $_GET["key"];
$ticket_type = "resetpassword";

if($ticket_id = $MODEL->USER->validateTicket($user_id, $ticket_key, $ticket_type))
{
	if($_POST["action"] == "Kaydet")
	{
		if($MODEL->USER->changePassword($user_id, $_POST["password"]))
		{
			$MODEL->USER->closeTicket($ticket_id);
			header("Location:login.php");
			exit;
		}
		else
		{
			$resultText = $MODEL->USER->error;
		}
	}
	
	$newpassword->render();
}
else
{
	header("Location:login.php");
}

