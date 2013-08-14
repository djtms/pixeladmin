<?php	require_once 'includes.php';

if($_POST["admin_action"] == "Kaydet")
{
	global $ADMIN;
	extract($_POST, EXTR_OVERWRITE);
	$error = false;
	
	$password = isset($password) ? $password : null;
	$user = $ADMIN->AUTHENTICATION->authenticated_user;
	$postedEmail = $email;
	$userEmail = $user->email;
	
	
	if(!$ADMIN->VALIDATE->validateEmail($email))
	{
		$error = true;
		$message = 'Geçerli bir "E-Posta" girin!';
	}
	else if($postedEmail != $userEmail) // e-mail adresini değiştirip güncelle
	{
		if($ADMIN->USER->getUserByEmail($postedEmail))
		{
			$error = true;
			$message = 'Girdiğiniz "E-Posta" kullanımda!';
		}
		else if(!$ADMIN->USER->updateUser($user_id, $image_id, $displayname, $birthday, $first_name, $last_name, $email, $phone, $password))
		{
			$error = true;
			$message = "Hata oluştu.";
		}
		else
		{
			$message = "Profil Bilgileriniz Güncellendi.";
		}
	}	
	else if(!$ADMIN->USER->updateUser($user_id, $image_id, $displayname, $birthday, $first_name, $last_name, $email, $phone, $password))
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
$profile->user = $ADMIN->AUTHENTICATION->authenticated_user;
$profile->render();

