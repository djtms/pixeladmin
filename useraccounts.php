<?php

global $MODEL;

$data = $MODEL->USER->listUsers();

if($_GET["delete"] > 0)
{
	$user_id = $_GET["delete"];
	$user = $MODEL->USER->getUserById($user_id);
	$logged_user_permission = $MODEL->USER->loggedInUser->user_type;
	$delete_user_permission = $user->user_type;
	
	if($logged_user_permission > $delete_user_permission)
	{
		if($MODEL->USER->deleteUser($user_id))
		{
			postMessage("Kullanıcı başarıyla silindi!");
			header("Location:admin.php?page=useraaccounts");
			exit;
		}
		else
		{
			postMessage("Hata Oluştu! Lütfen tekrar deneyin!", true);
		}
	}
	else
	{
		postMessage("Bu işlem için yeterli yetkiniz yok!", true);
	}
}

dataGrid($data, "Kullanıcı Hesapları", "user_accounts", "<%displayname%>", "admin.php?page=add_useraccount", "admin.php?page=edit_useraccount", "admin.php?page=useraaccounts&delete=<%user_id%>");