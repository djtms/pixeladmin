<?php	require_once 'includes.php';

if($_POST["admin_action"] == "save_profile_info")
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
		$message = $GT->GECERLI_EPOSTA_ADRESI_GIRIN;
	}
	else if($postedEmail != $userEmail) // e-mail adresini değiştirip güncelle
	{
		if($ADMIN->USER->getUserByEmail($postedEmail))
		{
			$error = true;
			$message = $GT->EPOSTA_ADRESI_KULLANIMDA;
		}
		else if(!$ADMIN->USER->updateUser($user_id, $image_id, $displayname, $birthday, $first_name, $last_name, $email, $phone, $password))
		{
			$error = true;
			$message = $GT->HATA_OLUSTU;
		}
		else
		{
			$message = $GT->PROFIL_BILGILERINIZ_GUNCELLENDI;
		}
	}
	else if(!$ADMIN->USER->updateUser($user_id, $image_id, $displayname, $birthday, $first_name, $last_name, $email, $phone, $password))
	{
		$error = true;
		$message = $GT->HATA_OLUSTU;
	}
	else
	{
        $message = $GT->PROFIL_BILGILERINIZ_GUNCELLENDI;
	}
	postMessage($message,$error);
}


$profile->addScript("js/pages/profile.js");
$profile->user = $ADMIN->AUTHENTICATION->authenticated_user;
$profile->render();

