<?php

if($_GET["user"] > 0)
{
	$user_id = $_GET["user"];
	$useraccount = $ADMIN->USER->getUserById($user_id);
	setGlobal("useraccount", $useraccount);
	
	
	if($_POST["admin_action"] == "Kaydet")
	{
		$ADMIN->USER_ROLE->deleteUserRolesByUser($user_id);
		$user_roles = $_POST["user_roles"];
		$user_role_count = sizeof($user_roles);
		
		for($i=0; $i<$user_role_count; $i++)
		{
			$ADMIN->USER_ROLE->addUserRole($user_id, $user_roles[$i]);
		}
		
		// Yetkileri tekrar al
		$ADMIN->AUTHORIZATION->authorize();
	}
	
	$roles = $ADMIN->ROLE->listRoles();
	$user_roles_html = dataGrid($roles, "Kullanıcı Rolleri", "userRolesList", "<input type='checkbox' name='user_roles[]' value='{%role_id%}' /> {%role_name%}", null, null, null);
	
	$user_roles = $ADMIN->USER_ROLE->listUserRolesByUser($user_id);
	setGlobal("admin_user_roles_list", $user_roles);
	addScript("js/pages/edit_user_account.js");	
	$edit_useraccount->render();
}
else
{
	postMessage("Kullanıcı bulunamadı!", true);
	header("Location:admin.php?page=useraccounts");
	exit;
}

