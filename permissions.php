<?php

if($_GET["delete"] > 0)
{
	if($ADMIN->PERMISSION->deletePermission($_GET["delete"]))
	{
		postMessage("Başarıyla Silindi!");
		header("Location:admin.php?page=user_permissions");
		exit;
	}
	else
	{
		postMessage("Hata Oluştu!", true);
	}
}

$permissions = $ADMIN->PERMISSION->listPermissions();

dataGrid($permissions, "Kullanıcı Yetkileri", "permissionsList", "{%permission_name%}", "admin.php?page=add_permission", "admin.php?page=edit_permission&id={%permission_id%}", "admin.php?page=user_permissions&delete={%permission_id%}");
