<?php	require_once 'includes.php';

if($_POST["admin_action"] == "Kaydet")
{
	global $ADMIN;
	$error = false;
	
	$password = isset($_POST["password"]) ? $_POST["password"] : null;
	$user = $ADMIN->USER->loggedInUser;
	$postedEmail = $_POST["email"];
	$userEmail = $user->email;
	
	
	if(!$ADMIN->VALIDATE->validateEmail($_POST["email"]))
	{
		$error = true;
		$message = 'Geçerli bir "E-Posta" girin!';
	}
	else if($postedEmail != $userEmail)
	{
		if($ADMIN->USER->getUserByEmail($postedEmail))
		{
			$error = true;
			$message = 'Girdiğiniz "E-Posta" kullanımda!';
		}
		else if(!$ADMIN->USER->updateUser($_POST["user_id"], $_POST["image_id"], $_POST["displayname"], $_POST["birthday"], $_POST["email"], $password))
		{
			$error = true;
			$message = "Hata oluştu.";
		}
		else
		{
			$message = "Profil Bilgileriniz Güncellendi.";
		}
	}	
	else if(!$ADMIN->USER->updateUser($_POST["user_id"], $_POST["image_id"], $_POST["displayname"], $_POST["birthday"], $_POST["email"], $password))
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
$profile->user = $ADMIN->USER->loggedInUser;
$profile->render();

