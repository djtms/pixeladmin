<?php	require_once 'includes.php';

if($_POST["admin_action"] == "save_profile_info")
{
	global $ADMIN;
	extract($_POST, EXTR_OVERWRITE);
	$error = false;
	
	$user = $ADMIN->AUTHENTICATION->authenticated_user;

    $userObj = new \com\admin\system\objects\UserObject($_POST);

	if(!$ADMIN->VALIDATE->validateEmail($email)) {
		$error = true;
		$message = $GT->GECERLI_EPOSTA_ADRESI_GIRIN;
	}
	else if(($user->email != $email) && $ADMIN->USER->getUserByEmail($email)) {
        // e-mail adresi değişmişse ve farklı bir kullanıcı kullanıyorsa hata oluştur
        $error = true;
        $message = $GT->EPOSTA_ADRESI_KULLANIMDA;
	}
	else if(!$ADMIN->USER->updateUser($userObj)) {
		$error = true;
		$message = $GT->HATA_OLUSTU;
	}
	else {
        $ADMIN->AUTHENTICATION->authenticated_user = $ADMIN->USER->getUserById($user->user_id);
        $message = $GT->PROFIL_BILGILERINIZ_GUNCELLENDI;
	}
	postMessage($message,$error);
}

$profile->user = $ADMIN->AUTHENTICATION->authenticated_user;
$profile->addScript("js/pages/profile.js");


// generate gender html
$profile->genderHtml  = '<option value="male" ' . ($profile->user->gender == "male" ? ' selected="selected" ' : '') . '>Erkek</option>';
$profile->genderHtml .= '<option value="female" ' . ($profile->user->gender == "female" ? ' selected="selected" ' : '') . '>Kız</option>';



$profile->render();

