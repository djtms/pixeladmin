<?php require_once 'includes.php';

$private_key = '6LdIQM0SAAAAAHAEnAYlIrwRKfjLRh2a8oIY_PmW';
$user_id = $_GET["user"];
$ticket_key = $_GET["key"];
$ticket_type = $_GET["type"];
$username = trim($_POST["username"]);

if($_POST["action"] == "checkusername")
{
	checkUsername(); // pa-users.php içinde tanımlı, bu sayfada login olmadığım için panelin kendi ajax standardını kullanamıyorum onun için soruyu bu şekilde yapıyoruz
	exit;
}
if($ticket_id = $MODEL->USER->validateTicket($user_id, $ticket_key, $ticket_type))
{	
	if($_POST["action"] == "complete_registration")
	{
		$captcha = recaptcha_check_answer($private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		
		if(strlen($username) < 6)
		{
			$resultText = '"Kullanıcı Adı" en az 6 karakterden oluşmalı!';
		}
		else if($MODEL->USER->getUserByUsername($username))
		{
			$resultText = "Lütfen farklı bir kullanıcı adı girin!";
		}
		else if (!$captcha->is_valid)
		{
			$resultText = "Captcha Hatası!";
		}
		else
		{
			$password = $_POST["password"];
			
			if($MODEL->USER->completeRegistration($user_id, $username, $password) && $MODEL->USER->closeTicket($ticket_id))
			{
				postMessage("Kaydınız Başarıyla Gerçekleşti!");
				$MODEL->USER->login($username, $password);
				header("Location:admin.php?page=profile");
				exit;
			}	
		}
	}
	
	$complete_registration->render();
}
else
{
	header("Location:login.php");
}