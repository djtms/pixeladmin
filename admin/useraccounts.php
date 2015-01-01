<?php

global $ADMIN;

$data = $ADMIN->USER->listUsers();

if($_GET["delete"] > 0) {
	$user_id = $_GET["delete"];
	$user = $ADMIN->USER->getUserById($user_id);

    // Kullanıcı kendini silmeye kalkarsa uyarı ver
    if($ADMIN->AUTHENTICATION->authenticated_user->user_id == $user_id){
        postMessage($GT->UYARI_KULLANICI_KENDINI_SILEMEZ, true);
        header("Location:admin.php?page=useraccounts");
        exit;
    }

    // Silinecek kullanıcı Admin ise ve Admin kullanıcı sayısı 2 den az ise silme işlemini gerçekleştirme
    if($ADMIN->USER_ROLE->checkIfUserHasRole($user_id, 1) && $ADMIN->USER_ROLE->getUserCountByRole(1) < 2){
        postMessage($GT->UYARI_MIN_BIR_ADMIN_OLMALI, true);
        header("Location:admin.php?page=useraccounts");
        exit;
    }
	
	if($ADMIN->USER->deleteUser($user_id)) {
		postMessage($GT->BASARIYLA_SILINDI);
		header("Location:admin.php?page=useraccounts");
		exit;
	}
	else {
		postMessage($GT->BEKLENMEDIK_HATA, true);
	}
}

echo dataGrid($data, $GT->KULLANICI_HESAPLARI, "user_accounts", "{%displayname%}", "admin.php?page=add_user", "admin.php?page=edit_useraccount&user={%user_id%}", "admin.php?page=useraccounts&delete={%user_id%}");