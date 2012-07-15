<?php

class PA_PERMISSION extends DB
{
	private $table;
	public $error = array();
	
	function PA_PERMISSION()
	{
		parent::DB();
		$this->table = $this->tables->permission;
	}
	
	function listPermissions()
	{
		return $this->get_rows("SELECT * FROM {$this->table} ORDER BY order_num");
	}
	
	function listPermissionsByParentAsArrayTree($permission_parent = -1, $list_sub_permissions = true)
	{
		$permission_array = array();
	
		if($permission_array = $this->get_rows("SELECT * FROM {$this->table} WHERE permission_parent=?", array($permission_parent)))
		{
			if($list_sub_permissions)
			{
				$array_length = sizeof($permission_array);
				for($i = 0; $i<$array_length; $i++)
				{
				$permission_array[$i]->sub = $this->listPermissionsByParentAsArrayTree($permission_array[$i]->permission_id, true);
				}
	
				return $permission_array;
			}
			else
			{
				return $permission_array;
			}
		}
		else
		{
			return false;
		}
	}

	function listPermissionsByParentAsHtmlTree($permission_parent = "-1", $list_sub_permissions = true)
	{
		if($permission_array = $this->get_rows("SELECT * FROM {$this->table} WHERE permission_parent=?", array($permission_parent)))
		{
			$permission_html = "<ul>";
			$array_length = sizeof($permission_array);
				
			for($i=0; $i<$array_length; $i++)
			{
				$permission_html .= "<li permission_id='" . $permission_array[$i]->permission_id . "'>";
				$permission_html .= "<span>" . $permission_array[$i]->permission_name . "</span>";
	
				if($list_sub_permissions)
				{
					$permission_html .= $this->listPermissionsByParentAsHtmlTree($permission_array[$i]->permission_id, true);
				}
	
				$permission_html .= "</li>";
			}
				
			$permission_html .= "</ul>";
			return $permission_html;
		}
		else
		{
			return "";
		}
	}
	
	function listPermissionsByParentAsTreeGrid($permission_parent = "-1", $list_sub_permissions = true, $editable=true)
	{
		$listHtml  = '<div id="permissionsList" class="treeGridOuter">';
		$listHtml .= $this->listAsTreeHtmlList($permission_parent, $list_sub_permissions, $editable);
		$listHtml .= '</div>';
		
		return $listHtml;
	}
	
	function selectPermission($permission_id)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE permission_id=?", array($permission_id));
	}
	
	function addPermission($permission_name, $permission_url, $permission_parent=-1)
	{
		return $this->insert($this->table, array("permission_name"=>$permission_name, "permission_url"=>$permission_url, "permission_parent"=>$permission_parent));
	}
	
	function updatePermission($permission_id, $permission_name, $permission_url, $permission_parent)
	{
		return $this->update($this->table, array("permission_name"=>$permission_name, "permission_url"=>$permission_url, "permission_parent"=>$permission_parent), array("permission_id"=>$permission_id));
	}
	
	// TODO: permission silerken, varsa sildiğin permission'ın altındaki permissionlara ne olacağını düşün
	function deletePermission($permission_id)
	{
		global $ADMIN;
		
		if($this->beginTransaction())
		{
			if($this->execute("DELETE FROM {$this->table} WHERE permission_id=?", array($permission_id)) && 
				$ADMIN->ROLE_PERMISSION->deleteRolePermissionByPermissionId($permission_id) &&
				$ADMIN->GROUP_PERMISSION->deleteGroupPermissionByPermissionId($permission_id))
			{
				$this->commit();
				return true;
			}
			else
			{
				$this->error[] = "Hata: Transaction içindeki sql işlemlerinden en az birinde bir hata gerçekleşti! Dosya: " . __FILE__ . " Satır: " . __LINE__;
				$this->rollBack();
				return false;
			}
		}
		else
		{
			$this->error[] = "Hata: Transanction başlatılamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
			return false;
		}
	}
	
	function setPermissionOrderNum($permission_id, $order_num, $permission_parent=-1)
	{
		$permission_parent = $permission_parent > 0 ? $permission_parent : -1;
		return $this->execute("UPDATE {$this->table} SET permission_parent=?, order_num=? WHERE permission_id=?", array($permission_parent, $order_num, $permission_id));
	}
	
	
	
	/* PRIVATE FUNCTIONS */
	//--------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------
	private function listAsTreeHtmlList($permission_parent, $list_sub_permissions, $editable=true)
	{
		if($permission_array = $this->get_rows("SELECT * FROM {$this->table} WHERE permission_parent=? ORDER BY order_num ASC", array($permission_parent)))
		{
			$permission_html = '<ul class="itemsList">';
			$array_length = sizeof($permission_array);
		
			for($i=0; $i<$array_length; $i++)
			{
				$permission_html .= "<li id='order_" . $permission_array[$i]->permission_id . "' permission_id='" . $permission_array[$i]->permission_id . "' >";
				$permission_html .= "<div class='item'>";
				if(!$editable && ($permission_array[$i]->permission_url != ""))
				{
					$permission_html .= "<input type='checkbox' name='permission_checked_" . $permission_array[$i]->permission_id . "' value='permission_checked' />";
				}
				$permission_html .= "<label class='text' style='clear:none !important;'>" . $permission_array[$i]->permission_name . "</label>";
				if($editable)
				{
					$permission_html .= "<div class='rowEditButtonsOuter'><a class='crossBtn' href='admin.php?page=permissions&delete=" . $permission_array[$i]->permission_id . "' onclick='return false;'></a>";
					$permission_html .= "<a href='admin.php?page=edit_permission&id=" . $permission_array[$i]->permission_id . "' class='editBtn'></a></div>";
				}
				$permission_html .= "</div>";
				
				if($list_sub_permissions)
				{
					$permission_html .= $this->listAsTreeHtmlList($permission_array[$i]->permission_id, true, $editable);
				}
				
				$permission_html .= "</li>";
			}
		
			$permission_html .= "</ul>";
			return $permission_html;
		}
		else
		{
			return "";
		}
	}
}