<?php require_once 'includes.php'; 

if($_POST["admin_action"] == "Sıfırla")
{
	$username_or_email = $_POST["username_or_email"];
	if($ADMIN->USER->openResetPasswordTicket($username_or_email))
	{
	 	$resultText = "\"Parola Değiştirme\" maili adresinize gönderildi!";
	}
	else
	{
		$resultText = $ADMIN->USER->error;	
	}
}

$resetpassword->addScript("js/pages/login.js");
$resetpassword->render();