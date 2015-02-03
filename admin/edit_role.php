<?php

$role_id = $_GET["id"] > 0 ? $_GET["id"] : -1;

if($_POST["admin_action"] == "save_role") {
	$error = false;
	
	if($role_id > 0)
	{
		if(!$ADMIN->ROLE->updateRole($role_id, $_POST["role_name"], $_POST["role_key"]))
		{
			postMessage($GT->HATA_OLUSTU, true);
			$error = true;
		}
	}
	else
	{
		if(!$role_id = $ADMIN->ROLE->addRole($_POST["role_name"], $_POST["role_key"]))
		{
			postMessage($GT->HATA_OLUSTU, true);
			$error = true;
		}
	}
	
	// Rol yetkilerini ata
	if(!$error)
	{
		// Admin de kullanılan sayfaları al
		global $pa_page_permission_info_array;

        $ADMIN->PERMISSION->getAllAdminPermissions();

		$pa_page_permission_info_array[] = (object)array("permission_key"=>"ADMIN_ADMINPANEL"); // Yönetim paneli sayfalarını ayıran başlığıda ekliyoruz. 

		$permissions = $ADMIN->PERMISSION->listPermissions();
		$permissions = array_merge($permissions, $pa_page_permission_info_array); // database den çektiğin permission tablosuna panelde kullandığın sayfalarıda ekle

		$permission_count = sizeof($permissions);
		if(true)//$ADMIN->ROLE_PERMISSION->deleteRolePermissionByRoleId($role_id))
		{
			for($i=0; $i<$permission_count; $i++)
			{
				$permission_key = $permissions[$i]->permission_key;
				if(isset($_POST["permission_checked_" . $permission_key]))
				{
					$ADMIN->ROLE_PERMISSION->addRolePermission($role_id, $permission_key);	
				}
			}
			
			// yetkiler güncellendikten sonra tekrar authorize ol
			$ADMIN->AUTHORIZATION->authorize();
			
			// Sayfayı yönlendir
			postMessage($GT->BASARIYLA_KAYDEDILDI);
			header("Location:admin.php?page=roles");
			exit;
		}
		else
		{
			postMessage($GT->ROL_YETKILERI_SILINEMEDI, true);
		}
	}
	//-------------------------------------------------------------------------------------------
}

setGlobal("user_permissions", $ADMIN->ROLE_PERMISSION->listRolePermissionsByRoleId($role_id));

$role = $ADMIN->ROLE->selectRole($role_id);
$ADMIN->PERMISSION->getAllAdminPermissions();
$permissions_html = $ADMIN->PERMISSION->listPermissionsByParentAsTreeGrid("", true, false);

addScript("js/pages/edit_role.js");
$edit_role->render();
