<?php 

extract($_POST,EXTR_SKIP);

if($admin_action == "inviteUser")
{
	if($user = $ADMIN->USER->getUserByEmail($email))
	{
		postMessage('Mail adresi kullanımda!',true);
	}
	else
	{
		if($ADMIN->USER->inviteAdminUser($displayname, $email, $user_type))
		{
			postMessage("Davetiyeniz başarıyla gönderildi!");
			header("Location:admin.php?page=useraaccounts");
			exit;
		}
		else
			postMessage("Hata Oluştu!",true);
	}
}
else if($admin_action == "checkUserStatusByEmail")
{
	if($user = $ADMIN->USER->getUserByEmail($email))
	{
		if($user->status == "active")
		{
			echo "already_registered";
		}
		else if($user->status == "invited")
		{
			echo "not_activated_account";
		}
	}
	else
	{
		echo "not_exist";
	}
	
	exit;
}
else if($admin_action == "resendinvitationmail")
{
	if($ADMIN->USER->reSendInvitationMail($email))
		echo "succeed";
	else
		echo "error";
	
	exit;
}

addScript("js/pages/invite_user.js");
$invite_user->render();