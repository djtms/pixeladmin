<?php

class PA_USER_GROUP extends DB
{
	private $table;
	
	function PA_USER_GROUP()
	{
		parent::DB();
		$this->table = $this->tables->user_group;
	}
	
	function addUserGroup($user_id, $group_id)
	{
		return $this->insert($this->table, array("user_id"=>$user_id, "group_id"=>$group_id));
	}
	
	function deleteUserGroupsByUser($user_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE user_id=?", array($user_id));	
	}
	
	function deleteUserGroupsByGroup($group_id)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE group_id=?", array($group_id));
	}
	
	function listUserGroupsByUser($user_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE user_id=?", array($user_id));
	}
	
	function listUserGroupsByGroup($group_id)
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE group_id=?", array($group_id));
	}
}