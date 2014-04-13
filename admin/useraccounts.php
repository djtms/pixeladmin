<?php

global $ADMIN;

$data = $ADMIN->USER->listUsers();

if($_GET["delete"] > 0) {
	$user_id = $_GET["delete"];
	$user = $ADMIN->USER->getUserById($user_id);
	
	if($ADMIN->USER->deleteUser($user_id)) {
		postMessage($GT->BASARIYLA_SILINDI);
		header("Location:admin.php?page=useraccounts");
		exit;
	}
	else {
		postMessage($GT->BEKLENMEDIK_HATA, true);
	}
}

echo dataGrid($data, $GT->KULLANICI_HESAPLARI, "user_accounts", "{%displayname%}", "admin.php?page=invite_user", "admin.php?page=edit_useraccount&user={%user_id%}", "admin.php?page=useraccounts&delete={%user_id%}");