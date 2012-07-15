<?php

$role_id = $_GET["id"] > 0 ? $_GET["id"] : -1;

if($_POST["admin_action"] == "Kaydet")
{
	$error = false;
	
	if($role_id > 0)
	{
		if(!$ADMIN->ROLE->updateRole($role_id, $_POST["role_name"]))
		{
			postMessage("Hata Oluştu!", true);
			$error = true;
		}
	}
	else
	{
		if(!$role_id = $ADMIN->ROLE->addRole($_POST["role_name"]))
		{
			postMessage("Hata Oluştu!", true);
			$error = true;
		}
	}
	
	// Rol yetkilerini ata
	if(!$error)
	{
		$permissions = $ADMIN->PERMISSION->listPermissions();
		$permission_count = sizeof($permissions);
		if($ADMIN->ROLE_PERMISSION->deleteRolePermissionByRoleId($role_id))
		{
			for($i=0; $i<$permission_count; $i++)
			{
				$permission_id = $permissions[$i]->permission_id;
				if(isset($_POST["permission_checked_" . $permission_id]))
				{
					$ADMIN->ROLE_PERMISSION->addRolePermission($role_id, $permission_id);	
				}
			}
			
			// yetkiler güncellendikten sonra tekrar authorize ol
			$ADMIN->AUTHORIZATION->authorize();
			
			// Sayfayı yönlendir
			postMessage("Başarıyla Kaydedildi!");
			header("Location:admin.php?page=roles");
			exit;
		}
		else
		{
			postMessage("Hata: Rol yetkileri silinemedi!", true);
		}
	}
	//-------------------------------------------------------------------------------------------
}

setGlobal("user_permissions", $ADMIN->ROLE_PERMISSION->listRolePermissionsByRoleId($role_id));

$role = $ADMIN->ROLE->selectRole($role_id);
$permissions_html = $ADMIN->PERMISSION->listPermissionsByParentAsTreeGrid("-1", true, false);

addScript("js/pages/edit_role.js");
$edit_role->render();
