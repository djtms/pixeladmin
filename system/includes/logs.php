<?php

function add_log($logText)
{
	global $MODEL;
	
	return $MODEL->LOG->add_log($logText,"log");
}

function delete_log($log_id)
{
	global $MODEL;
	
	return $MODEL->LOG->delete_log($log_id);
}

function select_log($log_id)
{
	global $MODEL;
	
	return $MODEL->LOG->select_log($log_id);
}

function list_logs($limit = -1)
{
	global $MODEL;
	
	return $MODEL->LOG->list_logs("log",$limit);
}