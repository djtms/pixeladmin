<?php 

extract($_POST,EXTR_SKIP);

if($admin_action == "inviteUser")
{
	if($user = $ADMIN->USER->getUserByEmail($email))
	{
		postMessage('Mail adresi kullanımda',true);
	}
	else
	{
		if($ADMIN->USER->inviteAdminUser($displayname, $email, $user_type))
			postMessage("Davetiyeniz başarıyla gönderildi!");
		else
			postMessage("Hata Oluştu!",true);
	}
}

$invite_user->render();