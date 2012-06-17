<?php

class PA_GROUP_PERMISSION extends DB
{
	private $table;
	
	function PA_GROUP_PERMISSION()
	{
		parent::DB();
		$this->table = $this->tables->group_permission;
	}
	
	function addGroupPermission($group_id, $permission_id)
	{
		return $this->insert($this->table, array("group_id"=>$group_id, "permission_id"=>$permission_id));
	}
	
	function deleteGroupPermissionByGroupId($group_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE group_id=?", array($group_id));
	}
	
	function deleteGroupPermissionByPermissionId($permission_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE permission_id=?", array($permission_id));
	}
	
	function listGroupPermissionsByGroupId($group_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE group_id=?", array($group_id));
	}
	
	function listGroupPermissionsByPermissionId($permission_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE permission_id=?", array($permission_id));
	}
}