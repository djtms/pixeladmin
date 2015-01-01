<?php

class PA_USER_ROLE extends DB
{
	private $table;
	
	function PA_USER_ROLE()
	{
		parent::DB();
		$this->table = $this->tables->user_role;
	}
	
	function listUserRolesByUser($user_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE user_id=?", array($user_id));
	}
	
	function listUserRolesByRole($role_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE role_id=?", array($role_id));
	}

    function checkIfUserHasRole($user_id, $role_id){
        if($this->get_row("SELECT * FROM {$this->table} WHERE user_id=? AND role_id=?", array($user_id, $role_id))){
            return true;
        }
        else{
            return false;
        }
    }

    function getUserCountByRole($role_id){
        return $this->get_value("SELECT COUNT(*) FROM {$this->table} WHERE role_id=?", array($role_id));
    }
	
	function addUserRole($user_id, $role_id)
	{
		return $this->insert($this->table, array("user_id"=>$user_id, "role_id"=>$role_id));
	}
	
	function deleteUserRolesByUser($user_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE user_id=?", array($user_id));
	}
	
	function deleteUserRolesByRole($role_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE role_id=?", array($role_id));
	}
}
