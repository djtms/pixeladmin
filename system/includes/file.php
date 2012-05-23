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
else if($_POST["admin_action"] == "rotateImage")
{
	if(rotateImage($_POST["file_id"], $_POST["degree"]) && ($big_thumb = $ADMIN->THUMB->getThumbUrl($_POST["file_id"], 420, 350)) && ($small_thumb = $ADMIN->THUMB->getThumbUrl($_POST["file_id"], 123, 87)))
	{
		$cacheKey = "?cache" . uniqid();
		echo json_encode(array("success"=>true, "big_thumb"=>$big_thumb . $cacheKey, "small_thumb"=>$small_thumb . $cacheKey, "response"=>"Başarıyla Güncellendi!"));
	}
	else
		echo json_encode(array("success"=>false, "response"=>"Hata Oluştu! 2"));

	exit;
}

function rotateImage($file_id, $degree)
{
	global $ADMIN;
	$file = getFileInfo($file_id);
	
	$ADMIN->IMAGE_PROCESSOR->load($file->url);
	$ADMIN->IMAGE_PROCESSOR->rotate($degree);
	
	return $ADMIN->IMAGE_PROCESSOR->save($file->url) && $ADMIN->THUMB->deleteFileThumbs($file_id);
}