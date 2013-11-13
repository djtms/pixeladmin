<?php
function deleteGallery($gallery_id){
	global $ADMIN;

	return $ADMIN->GALLERY->deleteGallery($gallery_id);
}

function listGalleryFiles($gallery_id, $limit = -1){
	global $ADMIN;

	return $ADMIN->GALLERY->listGalleryFiles($gallery_id, $limit, false);
}

function listGalleryFilesByPage($gallery_id, $limit, $offset){
	global $ADMIN;

	return $ADMIN->GALLERY->listGalleryFilesByPage($gallery_id, $limit, $offset, false);
}

function getGalleryFileCount($gallery_id){
	global $ADMIN;

	return $ADMIN->GALLERY->getGalleryFileCount($gallery_id, false);
}

function getFirstFileInGallery($gallery_id){
	global $ADMIN;

	return $ADMIN->GALLERY->selectFirstFileInGallery($gallery_id);
}

function getLastFileInGallery($gallery_id){
	global $ADMIN;

	return $ADMIN->GALLERY->selectLastFileInGallery($gallery_id);
}

function getNTHFileInGallery($gallery_id, $nthIndex){
	global $ADMIN;

	return $ADMIN->GALLERY->selectNTHFileInGallery($gallery_id, $nthIndex);
}

function addFileToGallery($file_id, $gallery_id){
    global $ADMIN;

    return $ADMIN->GALLERY->addFileToGallery($file_id, $gallery_id);
}

function deleteFileFromGallery($file_id, $gallery_id){
    global $ADMIN;

    return $ADMIN->GALLERY->deleteFileFromGallery($file_id, $gallery_id);
}

function deleteWholeFilesFromGallery($gallery_id){
    global $ADMIN;

    return $ADMIN->GALLERY->deleteWholeFilesFromGallery($gallery_id);
}

function setGalleryFileOrderNum($file_id, $gallery_id, $order_num){
    global $ADMIN;

    $ADMIN->GALLERY->setGalleryFileOrderNum($file_id, $gallery_id, $order_num);
}



if(in_admin){
	function createTemporaryGallery(){
		global $ADMIN;

		if($gallery_id = $ADMIN->GALLERY->createGallery())
			echo json_encode(array("gallery_id"=>$gallery_id));
		else
			echo json_encode(array("gallery_id"=>-1));
	}

	switch($_POST["admin_action"]){
		case("listGalleryFiles"):
			if($files = $ADMIN->GALLERY->listGalleryFiles($_POST["gallery_id"],-1,true)){
				$file_count = sizeof($files);
				for($i=0; $i<$file_count; $i++){
					if(!$files[$i]->thumb = $ADMIN->DIRECTORY->getThumbUrl($files[$i]->file_id, 123, 87, false, true, "center top", "FFFFFF"))
						$files[$i]->thumb = "../upload/files/system/exclamation.jpg";
				}
			}
			else{
				$files = array();
			}

			echo json_encode($files);
		exit;

		case("createTemporaryGallery"):
			createTemporaryGallery();
		exit;
	}

	function saveGallery(){
		global $ADMIN;

		if(is_array($_POST["galleries"]) && (sizeof($_POST["galleries"]) > 0)){
            $galleries = (object)$_POST["galleries"];
			foreach($galleries as $g){
				$g = json_decode($g);
				$gallery_id = $g->gallery_id;
				$filesInfo = $g->filesInfo;

				if(is_array($filesInfo) && (sizeof($filesInfo) > 0)){
					foreach($filesInfo as $f){
						if($f->status == "new"){
							$ADMIN->GALLERY->addFileToGallery($f->id, $gallery_id, $f->order);
						}
						else if($f->status == "deleted"){
							$ADMIN->GALLERY->deleteFileFromGallery($f->id, $gallery_id);
						}
						else{
							$ADMIN->GALLERY->setGalleryFileOrderNum($f->id, $gallery_id, $f->order);
						}
					}
				}

				$ADMIN->DB->execute("UPDATE gallery SET status='active' WHERE gallery_id=?",array($gallery_id));
			}
	
			return true;
		}
		else
			return false;
	}
}