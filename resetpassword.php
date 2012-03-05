<?php require_once 'includes.php'; 

if($_POST["action"] == "Sıfırla")
{
	$username_or_email = $_POST["username_or_email"];
	if($MODEL->USER->openResetPasswordTicket($username_or_email))
	{
	 	$resultText = "\"Parola Değiştirme\" maili adresinize gönderildi!";
	}
	else
	{
		$resultText = $MODEL->USER->error;	
	}
}

$resetpassword->addScript("js/pages/login.js");
$resetpassword->render();