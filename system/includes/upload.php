<?php 

if(in_admin)
{
	if($_POST["admin_action"] == "uploadFile")
	{
		adminFileEditorFileUpload();
		exit;
	}
	else if($_POST["admin_action"] == "changeThumbnailExceptFileTypeIsImage")
	{
		changeThumbnailExceptFileTypeIsImage($_POST["file_id"], $_FILES["thumbfile"]);
	}
}

function adminFileEditorFileUpload()
{
	global $ADMIN;
	global $allowedFileFormatsForUpload;

	if(!$ADMIN->VALIDATE->validateFileFormat($_FILES["uploadFile"]["name"], $allowedFileFormatsForUpload))
	{
		echo json_encode(array("error"=>true,"message"=>"Bu dosyayı yüklemek için yeterli izniniz yok!"));
		exit;
	}

	$file_id = $ADMIN->UPLOADER->uploadFile($_POST["directory"],$_FILES["uploadFile"]);
	$file = $ADMIN->DIRECTORY->selectFileById($file_id);

	$file->url = preg_replace( "/^" . preg_quote("../../","/") . "/","", $file->url);
	$file->error = false;

	
	echo json_encode($file);
}

function changeThumbnailExceptFileTypeIsImage($file_id, $thumbfile)
{
	global $ADMIN;

	$file = $ADMIN->FILE->selectFileById($file_id);
	
	if(($old_thumb_file = $ADMIN->FILE->selectFileById($file->thumb_file_id)) && ($old_thumb_file->access_type == "thumbnail"))
	{
		$ADMIN->FILE->deleteFileById($old_thumb_file->file_id);
		$ADMIN->FILE->deleteFileThumbs($file_id);
	}
	
	$thumb_file_id = $ADMIN->UPLOADER->uploadFile($file->directory, $thumbfile, "thumbnail");
	$ADMIN->DB->execute("UPDATE {$ADMIN->DB->tables->file} SET thumb_file_id=? WHERE file_id=?", array($thumb_file_id, $file->file_id));
	
	
	$file = $ADMIN->DIRECTORY->selectFileById($file_id);
	$file->url = preg_replace( "/^" . preg_quote("../../","/") . "/","", $file->url);
	$file->error = false;
	
	echo json_encode($file);
}

function uploadFile($file)
{
	global $ADMIN;
	$directory = "Harici_Dosyalar/";
	
	if(!is_dir($directory))
	{
		if(!$ADMIN->DIRECTORY->createDirectory($directory))
			return false;
	}
	
	return $ADMIN->UPLOADER->uploadFile($directory, $file);
}