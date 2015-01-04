<?php
extract($_POST,EXTR_SKIP);

if($admin_action == "addUser"){
    $_POST["visible_in_admin"] = 1;
    $_POST["displayname"] = $_POST["username"];
    $userData = new \com\admin\system\objects\UserObject($_POST);

	if($ADMIN->USER->addUser($userData, $_POST["user_roles"])){
		postMessage($GT->KULLANICI_EKLENDI);
		header("Location:admin.php?page=useraccounts");
		exit;
	}
	else{
		postMessage($GT->BEKLENMEDIK_HATA, true);
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



