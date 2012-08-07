<?php

if($_GET["delete"] > 0)
{
	if(in_array($_GET["delete"], array(1,2)))
	{
		postMessage("Bu işlemi gerçekleştirmek için yetkiniz bulunmamaktadır!", true);
	}
	else if($ADMIN->ROLE->deleteRole($_GET["delete"]))
	{
		postMessage("Başarıyla Silindi!");
		header("Location:admin.php?page=roles");
		exit;
	}
	else
	{
		postMessage("Hata Oluştu!", true);
	}
}

$data = $ADMIN->ROLE->listRoles();

echo dataGrid($data, "Roller", "rolesList", "{%role_name%}", "admin.php?page=add_role", "admin.php?page=edit_role&id={%role_id%}", "admin.php?page=roles&delete={%role_id%}");