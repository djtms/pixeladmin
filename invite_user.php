<?php 

extract($_POST,EXTR_SKIP);

if($action == "inviteUser")
{
	if($user = $MODEL->USER->getUserByEmail($email))
	{
		postMessage('Mail adresi kullanımda',true);
	}
	else
	{
		if($MODEL->USER->inviteAdminUser($displayname, $email, $user_type))
			postMessage("Davetiyeniz başarıyla gönderildi!");
		else
			postMessage("Hata Oluştu!",true);
	}
}

$invite_user->render();