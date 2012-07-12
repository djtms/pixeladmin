<?php
$permission_id = $_GET["id"] > 0 ? $_GET["id"] : -1;

if($_POST["admin_action"] == "Kaydet")
{
	extract($_POST, EXTR_OVERWRITE);
	
	if(trim($permission_name) == "")
	{
		postMessage("Hata: Lütfen \"Yetki Adı\" değerini doldurun!", true);	
	}
	else if(trim($permission_url) == "")
	{
		postMessage("Hata: Lütfen \"Yetki Adresi\" değerini doldurun!", true);
	}
	else if($permission_id > 0)
	{
		if($ADMIN->PERMISSION->updatePermission($permission_id, $permission_name, $permission_url, $permission_parent))
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
		if($ADMIN->PERMISSION->addPermission($permission_name, $permission_url, $permission_parent))
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


$permissionsList = $ADMIN->PERMISSION->listPermissions();
$permission = $ADMIN->PERMISSION->selectPermission($permission_id);
$permission_count = sizeof($permissionsList);

for($i=0; $i<$permission_count; $i++)
{
	// Eğer sıradaki permission zaten var olan ve şu an düzenlenmekte olan permission ise bir sonraki döngüye geç
	if($permission_id == $permissionsList[$i]->permission_id)
	{
		continue;	
	}
	
	if($permission)
	{
		$selected = $permissionsList[$i]->permission_id == $permission->permission_parent ? " selected='selected' " : "";
	}
	else
	{
		$selected = "";
	}
	
	$otherPermissionsHtml .= "<option value='" . $permissionsList[$i]->permission_id . "' {$selected} >";
	$otherPermissionsHtml .= $permissionsList[$i]->permission_name . "</option>";
}




$edit_permission->render();