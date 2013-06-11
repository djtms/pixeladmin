<?php

if($_POST["admin_action"] == "getFileInfoById"){
    if($file = $ADMIN->FILE->selectFileById($_POST["file"])){
        if(!$file->thumb = $ADMIN->DIRECTORY->getThumbUrl($file->file_id, 123, 87, false, true, "center top", "FFFFFF"))
            $file->thumb = "../upload/files/system/exclamation.jpg";
    }
    else{
        $file = array();
    }

    echo json_encode($file);
    exit;
}
else if($_POST["admin_action"] == "rotateImage"){
    if(rotateImage($_POST["file_id"], $_POST["degree"]) && ($big_thumb = $ADMIN->THUMB->getThumbUrl($_POST["file_id"], 420, 350)) && ($small_thumb = $ADMIN->THUMB->getThumbUrl($_POST["file_id"], 123, 87))){
        $cacheKey = "?cache" . uniqid();
        echo json_encode(array("success"=>true, "big_thumb"=>$big_thumb . $cacheKey, "small_thumb"=>$small_thumb . $cacheKey, "response"=>"Başarıyla Güncellendi!"));
    }
    else
        echo json_encode(array("success"=>false, "response"=>"Hata Oluştu! 2"));

    exit;
}
else if($_POST["admin_action"] == "getFileDetailThumb"){
    getFileDetailThumb(); exit;
}
else if($_POST["admin_action"] == "updateFileInfo"){
    updateFileInfo(); exit;
}
else if($_POST["admin_action"] == "cropImage"){
    cropImage(); exit;
}
else if($_POST["admin_action"] == "listCustomCroppedImages"){
    listCustomCroppedImages(); exit;
}


function getFileUrl($file_id){
	global $ADMIN;
	
	if($file = $ADMIN->DIRECTORY->selectFileUrlById($file_id))
		return $file;
	else
		return false;		
}

function getFileInfo($file_id){
	global $ADMIN;
	
	return $ADMIN->DIRECTORY->selectFileById($file_id);
}

function rotateImage($file_id, $degree){
	global $ADMIN;
	$file = getFileInfo($file_id);

	$ADMIN->IMAGE_PROCESSOR->load($file->url);
	$ADMIN->IMAGE_PROCESSOR->rotate($degree);

	return $ADMIN->IMAGE_PROCESSOR->save($file->url) && $ADMIN->THUMB->deleteFileThumbs($file_id);
}

function deleteFile($file_id){
    global $ADMIN;

    return $ADMIN->FILE->deleteFile($file_id);
}

function getFileDetailThumb(){
	global $ADMIN;

	$thumb = $ADMIN->DIRECTORY->getThumbInfo($_POST["fileId"], 420, 350, false, true, "center top", "FFFFFF");
	$editor_thumb = $ADMIN->DIRECTORY->getThumbUrl($_POST["fileId"], 123, 87, false, true, "center top", "FFFFFF");

	echo json_encode(array("thumb_url"=>$thumb->url, "editor_thumb"=>$editor_thumb, "thumb_file_id"=>$thumb->owner->thumb_file_id));
}

function updateFileInfo(){
	global $ADMIN;
    global $public_uploadurl;
    global $private_uploadurl;

    $file = $ADMIN->FILE->selectFileById($_POST["file_id"]);

    $root = ${$file->access_type . "_uploadurl"};


	$fixedurl = preg_replace("/^" . preg_quote($root,"/") . "/", "", $_POST["url"]);
	$checkedFileId = $ADMIN->DIRECTORY->getFileIdByUrl($fixedurl);

	if(($checkedFileId > 0) && ($checkedFileId != $_POST["file_id"]))
	    echo json_encode(array("error"=>true,"message"=>"varolan bir dosya adı girdiniz, lütfen başka bir isim girin!"));
	else if(!$ADMIN->DIRECTORY->updateFileInfo($_POST["file_id"], $_POST["basename"], $_POST["filename"],$_POST["thumb_file_id"]))
	    echo json_encode(array("error"=>true,"message"=>"bir hata oluştu, lütfen tekrar deneyin!"));
	else
	    echo json_encode(array("error"=>false,"message"=>"başarıyla kaydedildi!"));
}

function cropImage(){
	global $ADMIN;

	if($ADMIN->THUMB->cropImage($_POST["file_id"], $_POST["left"], $_POST["top"], $_POST["crop_width"], $_POST["crop_height"], $_POST["resize_width"], $_POST["resize_height"]))
	    echo json_encode(array("error"=>false));
	else
	    echo json_encode(array("error"=>true));
}

function listCustomCroppedImages(){
	global $ADMIN;

	if($thumbs = $ADMIN->THUMB->listCustomCroppedImages($_POST["file_id"]))
	{
		$list_thumb_url = $ADMIN->THUMB->getThumbUrl($_POST["file_id"], 98, 78, false);
		echo json_encode(array("error"=>false, "data"=>$thumbs, "list_thumb_url"=>$list_thumb_url));
	}
	else
	    echo json_encode(array("error"=>true, "data"=>"error happened!"));
}

