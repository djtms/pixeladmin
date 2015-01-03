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

    $userObj = new \com\admin\system\objects\UserObject($_POST);
	
	if(!$ADMIN->VALIDATE->validateEmail($email)) {
		$error = true;
		$message = $GT->GECERLI_EPOSTA_ADRESI_GIRIN;
	}
	else if(($postedEmail != $userEmail) && $ADMIN->USER->getUserByEmail($postedEmail)) {
        // e-mail adresi değişmişse ve farklı bir kullanıcı kullanıyorsa hata oluştur
        $error = true;
        $message = $GT->EPOSTA_ADRESI_KULLANIMDA;
	}
	else if(!$ADMIN->USER->updateUser($userObj)) {
		$error = true;
		$message = $GT->HATA_OLUSTU;
	}
	else {
        $ADMIN->AUTHENTICATION->authenticated_user = $userObj->toArray();
        $message = $GT->PROFIL_BILGILERINIZ_GUNCELLENDI;
	}
	postMessage($message,$error);
}


$profile->addScript("js/pages/profile.js");
$profile->user = $ADMIN->AUTHENTICATION->authenticated_user;
$profile->render();

