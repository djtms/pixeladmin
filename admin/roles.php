<?php

if($_GET["delete"] > 0)
{
	if(in_array($_GET["delete"], array(1,2)))
	{
		postMessage($GT->UYARI_YETKINIZ_YOK, true);
	}
	else if($ADMIN->ROLE->deleteRole($_GET["delete"]))
	{
		postMessage($GT->BASARIYLA_SILINDI);
		header("Location:admin.php?page=roles");
		exit;
	}
	else
	{
		postMessage($GT->HATA_OLUSTU, true);
	}
}

$data = $ADMIN->ROLE->listRoles();

echo dataGrid($data, $GT->ROLLER, "rolesList", "{%role_name%}", "admin.php?page=add_role", "admin.php?page=edit_role&id={%role_id%}", "admin.php?page=roles&delete={%role_id%}");