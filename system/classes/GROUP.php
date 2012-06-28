<?php
class PA_GROUP extends DB
{
	private $table;
	
	function PA_GROUP()
	{
		parent::DB();
		$this->table = $this->tables->group;
	}
	
	function addGroup($group_name)
	{
		return $this->insert($this->table, array("group_name"=>$group_name));
	}
	
	function updateGroup($group_id, $group_name)
	{
		return $this->execute("UPDATE {$this->table} SET group_name=? WHERE group_id=?", array($group_name, $group_id));
	}
	
	function setGroupOrderNum($group_id, $order_num)
	{
		return $this->execute("UPDATE {$this->table} SET order_num=? WHERE group_id=?", array($order_num, $group_id));
	}
	
	function selectGroup($group_id)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE group_id=?", array($group_id));
	}
	
	function listGroups()
	{
		return $this->get_rows("SELECT * FROM {$this->table} ORDER BY order_num");
	}
	
	function deleteGroup($group_id)
	{
		global $ADMIN;
		
		if($this->beginTransaction())
		{
			if($this->execute("DELETE FROM {$this->table} WHERE group_id=?", array($group_id)) &&
			$ADMIN->USER_GROUP->deleteUserGroupsByGroup($group_id) &&
			$ADMIN->GROUP_PERMISSION->deleteGroupPermissionByGroupId($group_id))
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
}