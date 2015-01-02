<?php

class PA_ROLE extends DB
{
	private $table;
	public $error = array();
	
	function PA_ROLE()
	{
		parent::DB();
		$this->table = $this->tables->role;
	}
	
	function listRoles()
	{
		return $this->get_rows("SELECT * FROM {$this->table} ORDER BY order_num");
	}

    /***
     * @param $role_name
     * @param $role_key unique olmalı
     * @return bool|string
     */
	function addRole($role_name, $role_key)
	{
		return $this->insert($this->table, array("role_name"=>$role_name, "role_key"=>$role_key));
	}

    function selectRoleByRoleKey($role_key){
        return $this->get_row("SELECT * FROM {$this->table} WHERE role_key=?", array($role_key));
    }
	
	function deleteRole($role_id)
	{
		global $ADMIN;
	
		if($this->beginTransaction())
		{
			if($this->execute("DELETE FROM {$this->table} WHERE role_id=?", array($role_id)) &&
			$ADMIN->ROLE_PERMISSION->deleteRolePermissionByRoleId($role_id) && 
			$ADMIN->USER_ROLE->deleteUserRolesByRole($role_id))
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
	
	function updateRole($role_id, $role_name, $role_key)
	{
        if($role = $this->selectRoleByRoleKey($role_key)){
            if($role->role_id != $role_id){
                $this->error[] = "Hata: Kullandığınız role_key başka bir role tarafından kullanılıyor!";
                return false;
            }
        }

		return $this->update($this->table, array("role_name"=>$role_name, "role_key"=>$role_key), array("role_id"=>$role_id));
	}
	
	function setRoleOrderNum($role_id, $order_num)
	{
		return $this->execute("UPDATE {$this->table} SET order_num=? WHERE role_id=?", array($order_num, $role_id));
	}
	
	function selectRole($role_id)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE role_id=?", array($role_id));
	}
}