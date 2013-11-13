<?php
class PA_MESSAGE extends DB
{
	private $table;
	
	function PA_MESSAGE()
	{
		parent::DB();
		
		$this->table = $this->tables->message;
	}


	function listMessages($status = "all", $limit=-1)
	{
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
		
		return $this->get_rows($query,null);
	}


	function getMessageCount($status = "all")
	{
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


		return $this->get_value("SELECT COUNT(messageId) FROM {$this->table} {$status}");
	}


	function selectMessage($messageId)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE messageId=?",array($messageId));
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
		$submitTime = date("Y-m-d H:i:s",time());
		return $this->insert($this->table,array("fromName"=>$fromName,"subject"=>$subject,"message"=>$message,"submitTime"=>$submitTime));
	}


	function setReadStatus($messageId,$status)
	{
		return $this->execute("UPDATE {$this->table} SET readStatus=? WHERE messageId=?",array($status,$messageId));
	}


	function deleteMessage($messageId)
	{
		return $this->execute("DELETE FROM {$this->table} WHERE messageId=?",array($messageId));
	}
}