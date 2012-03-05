<?php	require_once 'includes.php';

if($_POST["pa_action"] == "Kaydet")
{
	global $MODEL;
	$error = false;
	
	$password = isset($_POST["password"]) ? $_POST["password"] : null;
	$user = $MODEL->USER->loggedInUser;
	$postedEmail = $_POST["email"];
	$userEmail = $user->email;
	
	
	if(!$MODEL->VALIDATE->validateEmail($_POST["email"]))
	{
		$error = true;
		$message = 'Geçerli bir "E-Posta" girin!';
	}
	else if($postedEmail != $userEmail)
	{
		if($MODEL->USER->getUserByEmail($postedEmail))
		{
			$error = true;
			$message = 'Girdiğiniz "E-Posta" kullanımda!';
		}
		else if(!$MODEL->USER->updateUser($_POST["user_id"], $_POST["image_id"], $_POST["displayname"], $_POST["birthday"], $_POST["email"], $password))
		{
			$error = true;
			$message = "Hata oluştu.";
		}
		else
		{
			$message = "Profil Bilgileriniz Güncellendi.";
		}
	}	
	else if(!$MODEL->USER->updateUser($_POST["user_id"], $_POST["image_id"], $_POST["displayname"], $_POST["birthday"], $_POST["email"], $password))
	{
		$error = true;
		$message = "Hata oluştu.";
	}
	else
	{
		$message = "Profil Bilgileriniz Güncellendi.";
	}
	postMessage($message,$error);
}


$profile->addScript("js/pages/profile.js");
$profile->user = $MODEL->USER->loggedInUser;
$profile->render();

