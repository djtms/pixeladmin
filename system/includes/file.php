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

if($_POST["admin_action"] == "getFileInfoById")
{
	echo json_encode($ADMIN->FILE->selectFileById($_POST["file"]));
	exit;
}