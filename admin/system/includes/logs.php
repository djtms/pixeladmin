<?php

function add_log($logText)
{
	global $ADMIN;
	
	return $ADMIN->LOG->add_log($logText,"log");
}

function delete_log($log_id)
{
	global $ADMIN;
	
	return $ADMIN->LOG->delete_log($log_id);
}

function select_log($log_id)
{
	global $ADMIN;
	
	return $ADMIN->LOG->select_log($log_id);
}

function list_logs($limit = -1)
{
	global $ADMIN;
	
	return $ADMIN->LOG->list_logs("log",$limit);
}