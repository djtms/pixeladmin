<?php

class PA_ROLE_PERMISSION extends DB
{
	private $table;
	
	function PA_ROLE_PERMISSION()
	{
		parent::DB();
		$this->table = $this->tables->role_permission;
	}
	
	function addRolePermission($role_id, $permission_id)
	{
		return $this->insert($this->table, array("role_id"=>$role_id, "permission_id"=>$permission_id));
	}
	
	function deleteRolePermissionByRoleId($role_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE role_id=?", array($role_id));
	}
	
	function deleteRolePermissionByPermissionId($permission_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE permission_id=?", array($permission_id));
	}
	
	function listRolePermissionsByRoleId($role_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE role_id=?", array($role_id));
	}
	
	function listRolePermissionsByPermissionId($permission_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE permission_id=?", array($permission_id));
	}
}