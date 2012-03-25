<?php
abstract class PA_USER_TRACK
{
	private $table;
	public $trackKeyName;
	
	public function PA_USER_TRACK()
	{
		global $DB;
		global $sessionKeysPrefix;
		
		$this->table = $DB->tables->user_track;
		$this->trackKeyName = $sessionKeysPrefix . "_TRACKKEY";
	}
	
	function openTrack($user_id)
	{
		global $DB;
		global $track_wait_limit;
		
		$tracking_key = uniqid("", true) . "_TRKY";
		$user_session = session_id();
		$user_ip = $_SERVER["REMOTE_ADDR"];
		$start_time = currentDateTime();
		$end_time = date("Y-m-d H:i:s",(time() + $track_wait_limit));
		
		if($DB->insert($this->table, array("tracking_key"=>$tracking_key, "user_id"=>$user_id, "user_session"=>$user_session, "user_ip"=>$user_ip, "start_time"=>$start_time, "end_time"=>$end_time)))
		{
			$_SESSION[$this->trackKeyName] = $tracking_key;
		}
		else
			return false;
	}
	
	function closeTrack($tracking_key)
	{
		global $DB;
		
		$end_time = date("Y-m-d H:i:s",time());
		return $DB->execute("UPDATE {$this->table} SET end_time=?, status=? WHERE tracking_key=?", array($end_time, 'closed', $tracking_key));
	}
	
	function extendTrackEndTime($tracking_key)
	{
		global $DB;
		global $track_wait_limit;
		
		$end_time = date("Y-m-d H:i:s",(time() + $track_wait_limit));
		
		return $DB->execute("UPDATE {$this->table} SET end_time=? WHERE tracking_key=?", array('closed', $end_time));
	}
	
	function deleteTrackByTrackId($track_id)
	{
		global $DB;
		
		return $DB->execute("DELETE FROM {$this->table} WHERE track_id=?", array($track_id));
	}
	
	function deleteTrackByTrackingKey($tracking_key)
	{
		global $DB;
		
		$DB->execute("DELETE FROM {$this->table} WHERE tracking_key=?", array($tracking_key));
	}
	
	function deleteTracksByUserId($user_id)
	{
		global $DB;
		
		return $DB->execute("DELETE FROM {$this->table} WHERE user_id=?", array($user_id));
	}
	
	function selectTrackByTrackId($track_id)
	{
		global $DB;
		
		return $DB->get_row("SELECT * FROM {$this->table} WHERE track_id=?", array($track_id));
	}
	
	function selectTrackByTrackingKey($tracking_key)
	{
		global $DB;
		
		return $DB->get_row("SELECT * FROM {$this->table} WHERE tracking_key=?", array($tracking_key));
	}
}