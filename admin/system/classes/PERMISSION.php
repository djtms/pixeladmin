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
	
	function listPermissionsByParentAsArrayTree($permission_parent = "", $list_sub_permissions = true)
	{
		$permission_array = array();
	
		if($permission_array = $this->get_rows("SELECT * FROM {$this->table} WHERE permission_parent=?", array($permission_parent)))
		{
			if($list_sub_permissions)
			{
				$array_length = sizeof($permission_array);
				for($i = 0; $i<$array_length; $i++)
				{
				$permission_array[$i]->sub = $this->listPermissionsByParentAsArrayTree($permission_array[$i]->permission_key, true);
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

	function listPermissionsByParentAsHtmlTree($permission_parent = "", $list_sub_permissions = true)
	{		
		if($permission_array = $this->get_rows("SELECT * FROM {$this->table} WHERE permission_parent=?", array($permission_parent)))
		{
			$permission_html = "<ul>";
			$array_length = sizeof($permission_array);
				
			for($i=0; $i<$array_length; $i++)
			{
				$permission_html .= "<li permission_key='" . $permission_array[$i]->permission_key . "'>";
				$permission_html .= "<span>" . $permission_array[$i]->permission_name . "</span>";
	
				if($list_sub_permissions)
				{
					$permission_html .= $this->listPermissionsByParentAsHtmlTree($permission_array[$i]->permission_key, true);
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
	
	function listPermissionsByParentAsTreeGrid($permission_parent = "", $list_sub_permissions = true, $editable=true)
	{
		$listHtml  = '<div id="permissionsList" class="treeGridOuter">';
		
		if(in_admin)
		{
			$listHtml .= '<ul class="itemsList">';
			$listHtml .= '<li id="ADMIN_ADMINPANEL">';
			$listHtml .= '<div class="item">';
			if(!$editable)
			{
				$listHtml .= "<input type='checkbox' name='permission_checked_ADMIN_ADMINPANEL' value='permission_checked' />";
			}
			$listHtml .= '<label class="text" style="clear:none !important;">Yönetim Paneli</label></div>';
			$listHtml .= $this->listAsTreeHtmlListFromAdminPages(true, "ADMIN_ADMINPANEL", $list_sub_permissions, $editable);
			$listHtml .= '</li>';
			$listHtml .= '</ul>';
		}
		$listHtml .= '<div id="sortablePermissionLists">';
		$listHtml .= '<ul class="itemsList">';
		$listHtml .= $this->listAsTreeHtmlList(false, $permission_parent, $list_sub_permissions, $editable);
		$listHtml .= '</ul>';
		$listHtml .= '</div>';
		$listHtml .= '</div>';
		
		return $listHtml;
	}
	
	function selectPermission($permission_key)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE permission_key=?", array($permission_key));
	}
	
	function addPermission($permission_key, $permission_name, $permission_parent="")
	{
		return $this->insert($this->table, array("permission_key"=>$permission_key, "permission_name"=>$permission_name, "permission_parent"=>$permission_parent));
	}
	
	function updatePermission($old_permission_key, $new_permission_key, $permission_name, $permission_parent)
	{
		if($this->beginTransaction())
		{
			// Normal update fonksiyonumuz burada aynı column isimleri olduğu için çakışma yaşıyor o yüzden kendi queryimizi yazıyoruz
			$queryUpdatePermissionKey = "UPDATE {$this->table} SET permission_key=?, permission_name=?, permission_parent=? WHERE permission_key=?";
			$queryUpdatePermissionParent = "UPDATE {$this->table} SET permission_parent=? WHERE permission_parent=?";
			$queryUpdateRolePermissionPermissionKey = "UPDATE {$this->tables->role_permission} SET permission_key=? WHERE permission_key=?";
			$queryUpdateGroupPermissionPermissionKey = "UPDATE {$this->tables->group_permission} SET permission_key=? WHERE permission_key=?";
			
			if($this->execute($queryUpdatePermissionKey, array($new_permission_key, $permission_name, $permission_parent, $old_permission_key)) &&
			 $this->execute($queryUpdatePermissionParent, array($new_permission_key, $old_permission_key)) &&
			 $this->execute($queryUpdateRolePermissionPermissionKey, array($new_permission_key, $old_permission_key)) &&
			 $this->execute($queryUpdateGroupPermissionPermissionKey, array($new_permission_key, $old_permission_key)))
			{
				return $this->commit();
			}
			else
			{
				$this->rollBack();
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 
	 * İstenen permission'ı gerekli tablolardan siler. Silme esnasında eğer silinmek istenen permission'ın alt permission'ları varsa
	 * o permissionların permission_parent değerine silinen permission'ın permission_parent değerini atar. Yani kısaca alt permission'ları
	 * bi üst seviyeye çıkarır.
	 * @param string $permission_key
	 */
	function deletePermission($permission_key)
	{
		global $ADMIN;
		
		if($this->beginTransaction())
		{
			$permission = $this->selectPermission($permission_key);
						
			if($this->execute("UPDATE {$this->table} SET permission_parent=? WHERE permission_parent=?", array($permission->permission_parent, $permission_key)) &&
				$this->execute("DELETE FROM {$this->table} WHERE permission_key=?", array($permission_key)) && 
				$ADMIN->ROLE_PERMISSION->deleteRolePermissionByPermissionKey($permission_key) &&
				$ADMIN->GROUP_PERMISSION->deleteGroupPermissionByPermissionKey($permission_key))
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
	
	function setPermissionOrderNum($permission_key, $order_num, $permission_parent="")
	{
		return $this->execute("UPDATE {$this->table} SET permission_parent=?, order_num=? WHERE permission_key=?", array($permission_parent, $order_num, $permission_key));
	}
	
	
	
	/* PRIVATE FUNCTIONS */
	//--------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------
	private function listAsTreeHtmlList($scope_with_ul, $permission_parent, $list_sub_permissions, $editable=true)
	{
		if($permission_array = $this->get_rows("SELECT * FROM {$this->table} WHERE permission_parent=? ORDER BY order_num ASC", array($permission_parent)))
		{
			$permission_html = $scope_with_ul ? '<ul class="itemsList">' : "";
			$array_length = sizeof($permission_array);
		
			for($i=0; $i<$array_length; $i++)
			{
				$permission_html .= "<li id='order_" . $permission_array[$i]->permission_key . "' permission_key='" . $permission_array[$i]->permission_key . "' >";
				$permission_html .= "<div class='item'>";
				// Eğer checkbox ile seçme işlemi yapmak için listeliyorsak
				if(!$editable)
				{
					$permission_html .= "<input type='checkbox' name='permission_checked_" . $permission_array[$i]->permission_key . "' value='permission_checked' />";
				}
				$permission_html .= "<label class='text' style='clear:none !important;'>" . $permission_array[$i]->permission_name . "</label>";
				if($editable)
				{
					$permission_html .= "<div class='rowEditButtonsOuter'><a class='crossBtn' href='admin.php?page=permissions&delete=" . $permission_array[$i]->permission_key . "' onclick='return false;'></a>";
					$permission_html .= "<a href='admin.php?page=edit_permission&id=" . $permission_array[$i]->permission_key . "' class='editBtn'></a></div>";
				}
				$permission_html .= "</div>";
				
				if($list_sub_permissions)
				{
					$permission_html .= $this->listAsTreeHtmlList(true, $permission_array[$i]->permission_key, true, $editable);
				}
				
				$permission_html .= "</li>";
			}
		
			$permission_html .= $scope_with_ul ? "</ul>" : "";
			return $permission_html;
		}
		else
		{
			return "";
		}
	}
	
	private function listAsTreeHtmlListFromAdminPages($scope_with_ul, $permission_parent, $list_sub_permissions, $editable=true)
	{
		// Admin Panel de tanımlanan tüm sayların dizisini al
		global $pa_page_permission_info_array;
		
		// aramayı database yerine array içinde yapacağız
		$length = sizeof($pa_page_permission_info_array);
		$permission_array = array();
		for($i=0; $i<$length; $i++)
		{
			if($pa_page_permission_info_array[$i]->permission_parent == $permission_parent)
			{
				$permission_array[] = $pa_page_permission_info_array[$i];
			}
		}
	
		// Eğer arama sonucunda birşey bulunduysa html oluştur
		if(sizeof($permission_array) > 0)
		{
			$permission_html = $scope_with_ul ? '<ul class="itemsList">' : "";
			$array_length = sizeof($permission_array);
	
			for($i=0; $i<$array_length; $i++)
			{
				$permission_html .= "<li id='order_" . $permission_array[$i]->permission_key . "' permission_key='" . $permission_array[$i]->permission_key . "' >";
				$permission_html .= "<div class='item'>";
				// Eğer checkbox ile seçme işlemi yapmak için listeliyorsak
				if(!$editable)
				{
					$permission_html .= "<input type='checkbox' name='permission_checked_" . $permission_array[$i]->permission_key . "' value='permission_checked' />";
				}
				
				$permission_html .= "<label class='text' style='clear:none !important;'>" . $permission_array[$i]->permission_name . "</label>";
				$permission_html .= "</div>";
		
				if($list_sub_permissions)
				{
					$permission_html .= $this->listAsTreeHtmlListFromAdminPages(true, $permission_array[$i]->permission_key, false, $editable);
				}
		
				$permission_html .= "</li>";
			}
	
			$permission_html .= $scope_with_ul ? "</ul>" : "";
			return $permission_html;
		}
		else
		{
			return "";
		}
	}
}