<?php require_once 'includes.php'; 

if($_POST["admin_action"] == "reset")
{
	$username_or_email = $_POST["username_or_email"];
	if($ADMIN->USER->openResetPasswordTicket($username_or_email))
	{
	 	$resultText = $GT->PAROLA_DEGISTIRME_MAIL_GONDERILDI;
	}
	else
	{
		$resultText = $ADMIN->USER->error;	
	}
}

$resetpassword->addScript("js/pages/login.js");
$resetpassword->render();