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
<style>
	.sortableTreeList 
	{
		float:left;
		clear:left;
	}
	
	.sortableTreeList  ul
	{
		width: 200px;
		margin-left: 25px;
		height:auto !important;
	}
	
	.sortableTreeList li span
	{
		width:200px;
		height:20px;
		display:block;
		border: solid 1px;
		margin-top: -1px;
		
		border: 1px solid #d4d4d4;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		border-color: #D4D4D4 #D4D4D4 #BCBCBC;
		padding: 6px;
		margin: 0 0 3px 0;
		cursor: move;
		background: #f6f6f6;
		background: -moz-linear-gradient(top,  #ffffff 0%, #f6f6f6 47%, #ededed 100%);
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(47%,#f6f6f6), color-stop(100%,#ededed));
		background: -webkit-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
		background: -o-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
		background: -ms-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
		background: linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ededed',GradientType=0 );
	}
	
	.placeholder
	{	
		border: 1px dashed #888;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		background: #f0f0f0;
	}
	
	.mjs-nestedSortable-error {
		background: #fbe3e4;
		border-color: transparent;
	}
	
</style>
<script src="view/js/pages/edit_role.js"></script>
<form method="post">
	<label>Rol Adı:</label>
	<input type="text" name="role_name" value="<?php echo $role->role_name; ?>" />
	<label>Yetkileri:</label>
	<div class="sortableTreeList">
	<?php
	echo $ADMIN->PERMISSION->listPermissionsByParentAsHtmlTree(-1);
	?>
	</div>
	<input type="submit" name="admin_action" value="Kaydet" />
</form>
