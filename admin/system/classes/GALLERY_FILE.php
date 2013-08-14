<?php
abstract class PA_GALLERY_FILE extends DB{
	public $tableGalleryFile;
	public $tableThumb;
	public $tableFile;
	public $tableFileThumb;
	
	function PA_GALLERY_FILE(){
		parent::DB();
		
		$this->tableGalleryFile = $this->tables->gallery_file;
		$this->tableThumb = $this->tables->thumb;
		$this->tableFile = $this->tables->file;
		$this->tableFileThumb = $this->tables->file_thumb;
	}
	
	public function setGalleryFileOrderNum($file_id, $gallery_id, $order_num){
		return $this->execute("UPDATE {$this->tableGalleryFile} SET order_num=? WHERE gallery_id=? AND file_id=?",array($order_num, $gallery_id, $file_id));
	}
	
	public function listGalleryFiles($gallery_id, $limit=-1 , $listIfFileDeleted = true){
		$query  = "SELECT f.*, gf.file_id  FROM {$this->tableGalleryFile} AS gf ";
		$query .= ($listIfFileDeleted ? "LEFT" : "INNER") . " JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= "WHERE gf.gallery_id=? ORDER BY gf.order_num ASC ";
		$query .= ($limit > 0 ? "LIMIT 0,$limit" : "");
		
		return $this->get_rows($query,array($gallery_id));
	}
	
	public function listGalleryFilesByPage($gallery_id, $limit, $offset, $listIfFileDeleted = true){
		$query  = "SELECT f.*, gf.file_id  FROM {$this->tableGalleryFile} AS gf ";
		$query .= ($listIfFileDeleted ? "LEFT" : "INNER") . " JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= "WHERE gf.gallery_id=? ORDER BY gf.order_num ASC LIMIT $offset,$limit";
		
		return $this->get_rows($query,array($gallery_id));
	}
	
	public function selectFirstFileInGallery($gallery_id){
		$query  = "SELECT f.* FROM {$this->tableGalleryFile} AS gf ";
		$query .= "INNER JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= "WHERE gf.gallery_id=? ORDER BY gf.order_num ASC LIMIT 0,1";
		
		return $this->get_row($query,array($gallery_id));
	}
	
	public function selectNTHFileInGallery($gallery_id,$nthIndex){
		$query  = "SELECT f.* FROM {$this->tableGalleryFile} AS gf ";
		$query .= "INNER JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= "WHERE gf.gallery_id=? ORDER BY gf.order_num ASC LIMIT 0,$nthIndex";
		
		return $this->get_row($query,array($gallery_id));
	}
	
	public function selectLastFileInGallery($gallery_id){
		$query  = "SELECT f.* FROM {$this->tableGalleryFile} AS gf ";
		$query .= "INNER JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= "WHERE gf.gallery_id=? ORDER BY gf.order_num DESC LIMIT 0,1";
		
		return $this->get_row($query,array($gallery_id));
	}
	
	public function getGalleryFileCount($gallery_id, $listIfFileDeleted = true){
		$query  = "SELECT COUNT(*) FROM {$this->tableGalleryFile} AS gf ";
		$query .= ($listIfFileDeleted ? "LEFT" : "INNER") . " JOIN {$this->tableFile} AS f ON gf.file_id=f.file_id ";
		$query .= "WHERE gf.gallery_id=? ORDER BY gf.order_num ASC ";
		
		return $this->get_value($query,array($gallery_id));
	}

	public function addFileToGallery($file_id, $gallery_id, $order_num = 0){
		return $this->insert($this->tableGalleryFile, compact("gallery_id", "file_id", "order_num"));
	}
	
	public function deleteFileFromGallery($file_id, $gallery_id){
		return $this->execute("DELETE FROM {$this->tableGalleryFile} WHERE gallery_id=? AND file_id=?",array($gallery_id, $file_id));
	}
	
	public function deleteWholeFilesFromGallery($gallery_id){
		return $this->execute("DELETE FROM {$this->tableGalleryFile} WHERE gallery_id=?",array($gallery_id));
	}
}