<?php
function deleteGallery($galleryId)
{
	global $ADMIN;
	
	return $ADMIN->GALLERY->deleteGallery($galleryId);
}

function listGalleryFiles($galleryId,$limit = -1)
{
	global $ADMIN;
	
	return $ADMIN->GALLERY->listGalleryFiles($galleryId,$limit,false);
}

function listGalleryFilesByPage($galleryId,$limit, $offset)
{
	global $ADMIN;
	
	return $ADMIN->GALLERY->listGalleryFilesByPage($galleryId, $limit, $offset, false);
}

function getGalleryFileCount($galleryId)
{
	global $ADMIN;
	
	return $ADMIN->GALLERY->getGalleryFileCount($galleryId,false);
}

function getFirstFileInGallery($galleryId)
{
	global $ADMIN;
	
	return $ADMIN->GALLERY->selectFirstFileInGallery($galleryId);
}

function getLastFileInGallery($galleryId)
{
	global $ADMIN;
	
	return $ADMIN->GALLERY->selectLastFileInGallery($galleryId);
}

function getNTHFileInGallery($galleryId,$nthIndex)
{
	global $ADMIN;
	
	return $ADMIN->GALLERY->selectNTHFileInGallery($galleryId,$nthIndex);
}

if(in_admin)
{
	global $ADMIN;
	
	function createTemporaryGallery()
	{
		global $ADMIN;
	
		if($galleryId = $ADMIN->GALLERY->createGallery())
			echo json_encode(array("galleryId"=>$galleryId));
		else
			echo json_encode(array("galleryId"=>-1));
	}
	
	switch($_POST["admin_action"])
	{
		case("listGalleryFiles"):		
			if($files = $ADMIN->GALLERY->listGalleryFiles($_POST["galleryId"],-1,true)){
				$file_count = sizeof($files);
				for($i=0; $i<$file_count; $i++){
					if(!$files[$i]->thumb = $ADMIN->DIRECTORY->getThumbUrl($files[$i]->file_id, 123, 87, false, true, "center top", "FFFFFF"))
						$files[$i]->thumb = "../upload/system/exclamation.jpg";
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
	
	function saveGallery()
	{
		global $ADMIN;
		
		if(is_array($_POST["galleries"]) && (sizeof($_POST["galleries"]) > 0))
		{
			foreach((object)$_POST["galleries"] as $g)
			{
				$g = json_decode($g);
				$galleryId = $g->galleryId;
				$filesInfo = $g->filesInfo;
					
				$gallery = $ADMIN->GALLERY->selectGallery($galleryId);
					
				if(is_array($filesInfo) && (sizeof($filesInfo) > 0))
				{
					foreach($filesInfo as $f)
					{
						if($f->status == "new")
						{
							$ADMIN->GALLERY->addGalleryFile($galleryId, $f->id, $f->order);
						}
						else if($f->status == "deleted")
						{
							$ADMIN->GALLERY->deleteGalleryFile($galleryId, $f->id);
						}
						else
						{
							$ADMIN->GALLERY->updateGalleryFile($galleryId, $f->id, $f->order);
						}
					}
				}
					
				$ADMIN->DB->execute("UPDATE gallery SET status='active' WHERE gallery_id=?",array($galleryId));
			}
	
			return true;
		}
		else
			return false;
	}
}