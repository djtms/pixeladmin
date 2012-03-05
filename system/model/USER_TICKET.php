<?php

abstract class PA_USER_TICKET extends PA_USER_TRACK
{
	private $table;
	public $error;
	
	public function PA_USER_TICKET()
	{
		global $DB;
		
		$this->table = $DB->tables->user_ticket;
		parent::PA_USER_TRACK();
	}
	
	function openTicket($user_id, $ticket_type, $end_time = "000-00-00 00:00:00")
	{
		global $DB;
		
		$ticket_key = sha1(randomString(20)) . sha1(randomString(20)) . sha1(randomString(20)) . sha1(randomString(20));
		
		if($DB->insert($this->table, array("user_id"=>$user_id, "ticket_type"=>$ticket_type, "ticket_key"=>$ticket_key, "end_time"=>$end_time)))
		{
			return $DB->lastInsertId();
		}
		else
			return false;
	}
	
	function closeTicket($ticket_id)
	{
		global $DB;
		
		$end_time = currentDateTime();
		
		return $DB->execute("UPDATE {$this->table} SET status='closed', end_time=? WHERE ticket_id=?", array($end_time, $ticket_id));
	}
	
	function selectUserTicketsByTicketType($user_id, $ticket_type)
	{
		global $DB;
		
		return $DB->get_rows("SELECT * FROM {$this->table} WHERE user_id=? AND ticket_type=?", array($user_id, $ticket_type));
	}
	
	function selectTicket($ticket_id)
	{
		global $DB;
		
		return $DB->get_row("SELECT * FROM {$this->table} WHERE ticket_id=?", array($ticket_id));
	}
	
	function validateTicket($user_id, $ticket_key, $ticket_type)
	{
		global $DB;
		
		return $DB->get_value("SELECT ticket_id FROM {$this->table} WHERE user_id=? AND ticket_type=? AND ticket_key=? AND status='active'", array($user_id, $ticket_type, $ticket_key));
	}
}