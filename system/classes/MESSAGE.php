<?php
class PA_MESSAGE
{
	private $table;
	
	function PA_MESSAGE()
	{
		global $DB;
		
		$this->table = $DB->tables->message;
	}
	function listMessages($status = "all", $limit=-1)
	{
		global $DB;

		
		if($status == "all")
			$status = " readStatus='read' || readStatus='unread' || readStatus='saved' ";
		else if($status == "read")
			$status = " readStatus='read' ";
		else if($status == "unread")
			$status = " readStatus='unread' ";
		else if($status == "saved")
			$status = " readStatus='saved' ";
		else
			return array();
		
		$query = "SELECT *,DATE_FORMAT(submitTime,'%d.%b.%y - %H:%i:%s') AS submitTime FROM {$this->table} ";
		$query .= " WHERE {$status} ORDER BY submitTime DESC " . ($limit > 0 ? " LIMIT 0,$limit" : "");
		
		return $DB->get_rows($query,null);
	}
	function getMessageCount($status = "all")
	{
		global $DB;
		
		if($status == "all")
			$status = " WHERE readStatus='read' || readStatus='unread' || readStatus='saved' ";
		else if($status == "read")
			$status = " WHERE readStatus='read' ";
		else if($status == "unread")
			$status = " WHERE readStatus='unread' ";
		else if($status == "saved")
			$status = " WHERE readStatus='saved' ";
		else
			return "0";
		return $DB->get_value("SELECT COUNT(messageId) FROM {$this->table} {$status}");
	}
	function selectMessage($messageId)
	{
		global $DB;
		return $DB->get_row("SELECT * FROM {$this->table} WHERE messageId=?",array($messageId));
	}
	/**
	 * 
	 * mesajı database e kaydeder ve mesaj id umarasını döndürür
	 * @param (string) $fromName
	 * @param (string) $subject
	 * @param (string) $message
	 */
	function sendMessage($fromName,$subject,$message)
	{
		global $DB;		
		$submitTime = date("Y-m-d H:i:s",time());
		if($DB->insert($this->table,array("fromName"=>$fromName,"subject"=>$subject,"message"=>$message,"submitTime"=>$submitTime)))
			return $DB->lastInsertId();
		else
			return false;
	}
	function setReadStatus($messageId,$status)
	{
		global $DB;
		return $DB->execute("UPDATE {$this->table} SET readStatus=? WHERE messageId=?",array($status,$messageId));
	}
	function deleteMessage($messageId)
	{
		global $DB;	
		return $DB->execute("DELETE FROM {$this->table} WHERE messageId=?",array($messageId));
	}
}