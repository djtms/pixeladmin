<?php
function getFileUrl($file_id)
{
	global $ADMIN;
	
	if($file = $ADMIN->DIRECTORY->selectFileUrlById($file_id))
		return $file;
	else
		return false;		
}

function getFileInfo($file_id)
{
	global $ADMIN;
	
	return $ADMIN->DIRECTORY->selectFileById($file_id);
}