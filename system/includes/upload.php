<?php 

if(in_admin)
{
	if($_POST["admin_action"] == "uploadFile")
	{
		adminFileEditorFileUpload();
		exit;
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


	global $ADMIN;
	
	$file_id = $ADMIN->UPLOADER->uploadFile($_POST["directory"],$_FILES["uploadFile"]);
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