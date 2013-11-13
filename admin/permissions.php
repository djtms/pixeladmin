<?php

if($_POST["admin_action"] == "updatePermissionsSort")
{
	$sort = json_decode($_POST["sort"]);
	$sort_count = sizeof($sort);
	
	for($i=0; $i<$sort_count; $i++)
	{
		$s = $sort[$i];
	
		if(strlen($s->item_id) > 0)
		{
			$ADMIN->PERMISSION->setPermissionOrderNum($s->item_id, $s->left, $s->parent_id);
		}
	}
	echo "updated";
	exit;
}

if(strlen($_GET["delete"]) > 0)
{
	if($ADMIN->PERMISSION->deletePermission($_GET["delete"]))
	{
		postMessage("Başarıyla Silindi!");
		header("Location:admin.php?page=permissions");
		exit;
	}
	else
	{
		postMessage("Hata Oluştu!", true);
	}
}

echo "<h2>Kullanıcı Yetkileri</h2><a href='admin.php?page=add_permission' class='button' style='position:absolute; right: 338px; top:45px;'>Yeni Ekle</a>";
echo $ADMIN->PERMISSION->listPermissionsByParentAsTreeGrid();

?>
<script src="view/js/pages/permissions.js"></script>