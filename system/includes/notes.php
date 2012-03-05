<?php

function add_note($noteText)
{
	global $MODEL;
	
	return $MODEL->LOG->add_log($noteText,"note");
}

function delete_note($note_id)
{
	global $MODEL;
	
	return $MODEL->LOG->delete_log($note_id);
}

function select_note($note_id)
{
	global $MODEL;
	
	return $MODEL->LOG->select_log($note_id);
}

function list_notes($limit = -1)
{
	global $MODEL;
	
	return $MODEL->LOG->list_logs("note",$limit);
}