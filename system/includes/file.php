<?php
function getFileUrl($file_id)
{
	global $MODEL;
	
	if($file = $MODEL->DIRECTORY->selectFileUrlById($file_id))
		return $file;
	else
		return false;		
}

function getFileInfo($file_id)
{
	global $MODEL;
	
	return $MODEL->DIRECTORY->selectFileById($file_id);
}