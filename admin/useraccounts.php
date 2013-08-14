<?php

global $ADMIN;

$data = $ADMIN->USER->listUsers();

if($_GET["delete"] > 0)
{
	$user_id = $_GET["delete"];
	$user = $ADMIN->USER->getUserById($user_id);
	
	if($ADMIN->USER->deleteUser($user_id))
	{
		postMessage("Kullanıcı başarıyla silindi!");
		header("Location:admin.php?page=useraccounts");
		exit;
	}
	else
	{
		postMessage("Beklenmedi bir hata oluştu! Lütfen tekrar deneyin!", true);
	}
}

echo dataGrid($data, "Kullanıcı Hesapları", "user_accounts", "{%displayname%}", "admin.php?page=invite_user", "admin.php?page=edit_useraccount&user={%user_id%}", "admin.php?page=useraccounts&delete={%user_id%}");