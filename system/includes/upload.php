<?php 

if(in_admin)
	registerAjaxCall("uploadFile", "adminFileEditorFileUpload");

function adminFileEditorFileUpload()
{
	global $MODEL;
	global $allowedFileFormatsForUpload;

	if(!$MODEL->VALIDATE->validateFileFormat($_FILES["uploadFile"]["name"], $allowedFileFormatsForUpload))
	{
		echo json_encode(array("error"=>true,"message"=>"Bu dosyayı yüklemek için yeterli izniniz yok!"));
		exit;
	}


	global $MODEL;
	
	$file_id = $MODEL->UPLOADER->uploadFile($_POST["directory"],$_FILES["uploadFile"]);
	$file = $MODEL->DIRECTORY->selectFileById($file_id);

	$file->url = preg_replace( "/^" . preg_quote("../../","/") . "/","", $file->url);
	$file->error = false;

	
	echo json_encode($file);
}

function uploadFile($file)
{
	global $MODEL;
	$directory = "Harici_Dosyalar/";
	
	if(!is_dir($directory))
	{
		if(!$MODEL->DIRECTORY->createDirectory($directory))
			return false;
	}
	
	return $MODEL->UPLOADER->uploadFile($directory, $file);
} 