<?php

$role_id = $_GET["id"] > 0 ? $_GET["id"] : -1;

if($_POST["admin_action"] == "Kaydet")
{
	if($role_id > 0)
	{
		if($ADMIN->ROLE->updateRole($role_id, $_POST["role_name"]))
		{
			postMessage("Başarıyla Kaydedildi!");
			header("Location:admin.php?page=roles");
			exit;
		}
		else
		{
			postMessage("Hata Oluştu!");
		}
	}
	else
	{
		if($ADMIN->ROLE->addRole($_POST["role_name"]))
		{
			postMessage("Başarıyla Kaydedildi!");
			header("Location:admin.php?page=roles");
			exit;
		}
		else
		{
			postMessage("Hata Oluştu!");
		}
	}
}

$role = $ADMIN->ROLE->selectRole($role_id);
?>

<form method="post">
	<label>Rol Adı:</label>
	<input type="text" name="role_name" value="<?php echo $role->role_name; ?>" />
	<label>Yetkileri:</label>
	<?php
	
	//dataGrid($data, $gridTitle, $gridId, $rowTitleQuery, $addDataLink, $editDataLinkQuery, $deleteDataLinkQuery);
	
	?>
	<input type="submit" name="admin_action" value="Kaydet" />
</form>
