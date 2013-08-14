<?php

function add_note($noteText)
{
	global $ADMIN;
	
	return $ADMIN->LOG->add_log($noteText,"note");
}

function delete_note($note_id)
{
	global $ADMIN;
	
	return $ADMIN->LOG->delete_log($note_id);
}

function select_note($note_id)
{
	global $ADMIN;
	
	return $ADMIN->LOG->select_log($note_id);
}

function list_notes($limit = -1)
{
	global $ADMIN;
	
	return $ADMIN->LOG->list_logs("note",$limit);
}