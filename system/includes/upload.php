<?php 

if(in_admin)
{
	if($_POST["admin_action"] == "changeThumbnailExceptFileTypeIsImage"){
		changeThumbnailExceptFileTypeIsImage($_POST["file_id"], $_FILES["thumbfile"]);
	}
}

function changeThumbnailExceptFileTypeIsImage($file_id, $thumbfile){
	global $ADMIN;

	$file = $ADMIN->FILE->selectFileById($file_id);
	
	if(($old_thumb_file = $ADMIN->FILE->selectFileById($file->thumb_file_id)) && ($old_thumb_file->access_type == "private")){
		$ADMIN->FILE->deleteFile($old_thumb_file->file_id);
		$ADMIN->FILE->deleteFileThumbs($old_thumb_file->file_id);
		$ADMIN->FILE->deleteFileThumbs($file_id);
	}
	
	$thumb_file_id = $ADMIN->UPLOADER->uploadFile(-1, $thumbfile, null, "private");
	$ADMIN->DB->execute("UPDATE {$ADMIN->DB->tables->file} SET thumb_file_id=? WHERE file_id=?", array($thumb_file_id, $file->file_id));
	
	
	$file = $ADMIN->DIRECTORY->selectFileById($file_id);
	$file->url = preg_replace( "/^" . preg_quote("../../","/") . "/","", $file->url);
	$file->error = false;
	
	echo json_encode($file);
}

function uploadFile($file, $directory = "Harici_Dosyalar/", $rename=null, $access_type="public"){
	global $ADMIN;

    if($directory_id = $ADMIN->DIRECTORY->createDirectoryByPath($directory, $access_type)){
        return $ADMIN->UPLOADER->uploadFile($directory_id, $file, $rename, $access_type);
    }
    else{
        return false;
    }
}