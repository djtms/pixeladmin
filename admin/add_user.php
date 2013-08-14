<?php
extract($_POST,EXTR_SKIP);

if($admin_action == "addUser"){
	if($ADMIN->USER->addUser($username, $username, $email, $password)){
		postMessage("Yeni kullanıcı sisteme başarıyla eklenmiştir!");
		header("Location:admin.php?page=useraccounts");
		exit;
	}
	else{
		postMessage("* Beklenmedik bir hata oluştu, lütfen tekrar deneyin!", true);
	}
}
else if($admin_action == "checkUserStatusByUsername"){
	if($user = $ADMIN->USER->getUserByUsername($username)){
		echo "existing_user";
	}
	else{
		echo "not_exist";
	}

	exit;
}
else if($admin_action == "checkUserStatusByEmail"){
	if($user = $ADMIN->USER->getUserByEmail($email)){
		echo "existing_user";
	}
	else{
		echo "not_exist";
	}

	exit;
}



$roles = $ADMIN->ROLE->listRoles();
$add_user_roles_html =  dataGrid($roles, "", "userRolesList", "<input type='checkbox' name='user_roles[]' value='{%role_id%}' /> {%role_name%}", null, null, null);

addScript("js/pages/add_user.js");

$add_user->render();



