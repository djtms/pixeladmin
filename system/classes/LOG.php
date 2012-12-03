<?php
class PA_LOG extends DB
{
	private $table;
	private $table_user;
	
	function PA_LOG()
	{
		parent::DB();
		
		$this->table = $this->tables->log;
		$this->table_user = $this->tables->user;
	}
	
	function add_log($logText,$type)
	{
		global $ADMIN;
		
		$user = $ADMIN->AUTHENTICATION->authenticated_user;
		$user_id = $user->user_id;
		$date = currentDateTime();
		
		$temp = array("user_id"=>$user_id,"date"=>$date,"log"=>$logText,"type"=>$type);
		return $this->insert($this->table,$temp);
	}
	
	function delete_log($log_id)
	{
		return $this->execute("DELETE * FROM {$this->table} WHERE log_id=?",array($log_id));
	}
	
	function select_log($log_id)
	{
		$query = "SELECT l.*,u.displayname FROM {$this->table} AS l ";
		$query .= "LEFT JOIN {$this->table_user} AS u ON l.user_id=u.user_id WHERE l.log_id=?";
		
		return $this->get_row($query,array($log_id));
	}
	
	function list_logs($type, $limit=-1)
	{
		$query = "SELECT l.*,u.displayname, DATE_FORMAT(l.date,'%d.%b.%y - %H:%i:%s') AS formattedDate FROM {$this->table} AS l ";
		$query .= "LEFT JOIN {$this->table_user} AS u ON l.user_id=u.user_id WHERE l.type=? ";
		$query .= "ORDER BY l.log_id DESC ";
		$query .= ( $limit > 0 ? " LIMIT 0,$limit " : "");
		
		return $this->get_rows($query,array($type));
	}
}