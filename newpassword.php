<?php require_once 'includes.php'; 

global $ADMIN;
$user_id = $_GET["user"];
$ticket_key = $_GET["key"];
$ticket_type = "resetpassword";

if($ticket_id = $ADMIN->USER->validateTicket($user_id, $ticket_key, $ticket_type))
{
	if($_POST["admin_action"] == "Kaydet")
	{
		if($ADMIN->USER->changePassword($user_id, $_POST["password"]))
		{
			$ADMIN->USER->closeTicket($ticket_id);
			header("Location:login.php");
			exit;
		}
		else
		{
			$resultText = $ADMIN->USER->error;
		}
	}
	
	$newpassword->render();
}
else
{
	header("Location:login.php");
}

