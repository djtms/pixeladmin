<?php
abstract class PA_GALLERY_FILE extends DB
{
	public $tableGalleryFile;
	public $tableThumb;
	public $tableFile;
	public $tableFileThumb;
	
	function PA_GALLERY_FILE()
	{
		parent::DB();
		
		$this->tableGalleryFile = $this->tables->gallery_file;
		$this->tableThumb = $this->tables->thumb;
		$this->tableFile = $this->tables->file;
		$this->tableFileThumb = $this->tables->file_thumb;
	}
	
	public function updateGalleryFile($galleryId,$fileId,$orderNum)
	{
		return $this->execute("UPDATE {$this->tableGalleryFile} SET order_num=? WHERE gallery_id=? AND file_id=?",array($orderNum,$galleryId,$fileId));
	}
	
	public function listGalleryFiles($galleryId, $limit=-1 , $listIfFileDeleted = true)
	{
		global $uploadurl;
		
		$query  = "SELECT f.*,CONCAT('{$uploadurl}',f.url) AS url, gf.file_id  FROM {$this->tableGalleryFile} AS gf ";
		$query .= ($listIfFileDeleted ? "LEFT" : "INNER") . " JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= " WHERE gf.gallery_id=? ORDER BY gf.order_num ASC ";
		$query .= ($limit > 0 ? "LIMIT 0,$limit" : "");
		
		return $this->get_rows($query,array($galleryId));
	}
	
	public function listGalleryFilesByPage($galleryId, $limit, $offset, $listIfFileDeleted = true)
	{
		global $uploadurl;
		
		$query  = "SELECT f.*,CONCAT('{$uploadurl}',f.url) AS url, gf.file_id  FROM {$this->tableGalleryFile} AS gf ";
		$query .= ($listIfFileDeleted ? "LEFT" : "INNER") . " JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= " WHERE gf.gallery_id=? ORDER BY gf.order_num ASC LIMIT $offset,$limit";
		
		return $this->get_rows($query,array($galleryId));
	}
	
	public function selectFirstFileInGallery($galleryId)
	{
		
		global $uploadurl;
		
		$query  = "SELECT f.*,CONCAT('{$uploadurl}',f.url) AS url FROM {$this->tableGalleryFile} AS gf ";
		$query .= "INNER JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= " WHERE gf.gallery_id=? ORDER BY gf.order_num ASC LIMIT 0,1";
		
		return $this->get_row($query,array($galleryId));
	}
	
	public function selectNTHFileInGallery($galleryId,$nthIndex)
	{
		
		global $uploadurl;
		
		$query  = "SELECT f.*,CONCAT('{$uploadurl}',f.url) AS url FROM {$this->tableGalleryFile} AS gf ";
		$query .= "INNER JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= " WHERE gf.gallery_id=? ORDER BY gf.order_num ASC LIMIT 0,$nthIndex";
		
		return $this->get_row($query,array($galleryId));
	}
	
	public function selectLastFileInGallery($galleryId)
	{
		global $uploadurl;
		
		$query  = "SELECT f.*,CONCAT('{$uploadurl}',f.url) AS url FROM {$this->tableGalleryFile} AS gf ";
		$query .= "INNER JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= " WHERE gf.gallery_id=? ORDER BY gf.order_num DESC LIMIT 0,1";
		
		return $this->get_row($query,array($galleryId));
	}
	
	public function getGalleryFileCount($galleryId, $listIfFileDeleted = true)
	{	
		global $uploadurl;
		
		$query  = "SELECT COUNT(*) FROM {$this->tableGalleryFile} AS gf ";
		$query .= ($listIfFileDeleted ? "LEFT" : "INNER") . " JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= " WHERE gf.gallery_id=? ORDER BY gf.order_num ASC ";
		
		return $this->get_value($query,array($galleryId));
	}

	public function addGalleryFile($galleryId,$fileId,$orderNum = 0)
	{
		return $this->insert($this->tableGalleryFile,array("gallery_id"=>$galleryId,"file_id"=>$fileId,"order_num"=>$orderNum));
	}
	
	public function deleteGalleryFile($galleryId, $fileId)
	{
		return $this->execute("DELETE FROM {$this->tableGalleryFile} WHERE gallery_id=? AND file_id=?",array($galleryId, $fileId));
	}
	
	public function deleteGalleryFilesByGalleryId($galleryId)
	{
		return $this->execute("DELETE FROM {$this->tableGalleryFile} WHERE gallery_id=?",array($galleryId));
	}
	
}