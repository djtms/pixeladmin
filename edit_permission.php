<?php
$old_permission_key = $_GET["id"];

if($_POST["admin_action"] == "Kaydet")
{
	extract($_POST, EXTR_OVERWRITE);
	
	if(trim($permission_name) == "")
	{
		postMessage("Hata: Lütfen \"Yetki Adı\" değerini doldurun!", true);	
	}
	else if(strlen($old_permission_key) > 0)
	{
		if($ADMIN->PERMISSION->updatePermission($old_permission_key, $new_permission_key, $permission_name, $permission_parent))
		{
			postMessage("Başarıyla Kaydedildi!");
			header("Location:admin.php?page=permissions");
			exit;
		}
		else
		{
			postMessage("Hata Oluştu!", true);
		}
	}
	else
	{
		if($ADMIN->PERMISSION->addPermission($new_permission_key, $permission_name, $permission_parent))
		{
			postMessage("Başarıyla Kaydedildi!");
			header("Location:admin.php?page=permissions");
			exit;
		}
		else
		{
			postMessage("Hata Oluştu!", true);
		}
	}
}
else if($_POST["admin_action"] == "checkPermissionKey")
{
	if($permission = $ADMIN->PERMISSION->selectPermission($_POST["permission_key"]))
	{
		echo "key_exists";
	}
	else
	{
		echo "usable";
	}
	
	exit;
}


$permissionsList = $ADMIN->PERMISSION->listPermissions();
$permission = $ADMIN->PERMISSION->selectPermission($old_permission_key);
$permission_count = sizeof($permissionsList);

for($i=0; $i<$permission_count; $i++)
{
	// Eğer sıradaki permission zaten var olan ve şu an düzenlenmekte olan permission ise bir sonraki döngüye geç
	if($old_permission_key == $permissionsList[$i]->permission_key)
	{
		continue;	
	}
	
	if($permission)
	{
		$selected = $permissionsList[$i]->permission_key == $permission->permission_parent ? " selected='selected' " : "";
	}
	else
	{
		$selected = "";
	}
	
	$otherPermissionsHtml .= "<option value='" . $permissionsList[$i]->permission_key . "' {$selected} >";
	$otherPermissionsHtml .= $permissionsList[$i]->permission_name . "</option>";
}



addScript("js/pages/edit_permission.js");
$edit_permission->render();