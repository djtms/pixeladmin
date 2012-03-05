<?php
function deleteGallery($galleryId)
{
	global $MODEL;
	
	return $MODEL->GALLERY->deleteGallery($galleryId);
}

function listGalleryFiles($galleryId,$limit = -1)
{
	global $MODEL;
	
	return $MODEL->GALLERY->listGalleryFiles($galleryId,$limit,false);
}

function listGalleryFilesByPage($galleryId,$limit, $offset)
{
	global $MODEL;
	
	return $MODEL->GALLERY->listGalleryFilesByPage($galleryId, $limit, $offset, false);
}

function getGalleryFileCount($galleryId)
{
	global $MODEL;
	
	return $MODEL->GALLERY->getGalleryFileCount($galleryId,false);
}

function getFirstFileInGallery($galleryId)
{
	global $MODEL;
	
	return $MODEL->GALLERY->selectFirstFileInGallery($galleryId);
}

function getLastFileInGallery($galleryId)
{
	global $MODEL;
	
	return $MODEL->GALLERY->selectLastFileInGallery($galleryId);
}

function getNTHFileInGallery($galleryId,$nthIndex)
{
	global $MODEL;
	
	return $MODEL->GALLERY->selectNTHFileInGallery($galleryId,$nthIndex);
}

if(in_admin)
{
	global $MODEL;
	
	function createTemporaryGallery()
	{
		global $MODEL;
	
		if($galleryId = $MODEL->GALLERY->createGallery())
			echo json_encode(array("galleryId"=>$galleryId));
		else
			echo json_encode(array("galleryId"=>-1));
	}
	
	switch($_POST["action"])
	{
		case("listGalleryFiles"):		
			echo json_encode($MODEL->GALLERY->listGalleryFiles($_POST["galleryId"],-1,true));
		exit;
		
		case("createTemporaryGallery"):		
			createTemporaryGallery();	
		exit;
	}
	
	function saveGallery()
	{
		global $MODEL;
		global $DB;
	
		if(is_array($_POST["galleries"]) && (sizeof($_POST["galleries"]) > 0))
		{
			foreach((object)$_POST["galleries"] as $g)
			{
				$g = json_decode($g);
				$galleryId = $g->galleryId;
				$filesInfo = $g->filesInfo;
					
				$gallery = $MODEL->GALLERY->selectGallery($galleryId);
					
				if(is_array($filesInfo) && (sizeof($filesInfo) > 0))
				{
					foreach($filesInfo as $f)
					{
						if($f->status == "new")
						{
							$MODEL->GALLERY->addGalleryFile($galleryId, $f->id, $f->order);
						}
						else if($f->status == "deleted")
						{
							$MODEL->GALLERY->deleteGalleryFile($galleryId, $f->id);
						}
						else
						{
							$MODEL->GALLERY->updateGalleryFile($galleryId, $f->id, $f->order);
						}
					}
				}
					
				$DB->execute("UPDATE gallery SET status='active' WHERE gallery_id=?",array($galleryId));
			}
	
			return true;
		}
		else
		return false;
	}
}