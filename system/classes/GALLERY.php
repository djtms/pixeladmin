<?php
class PA_GALLERY extends PA_GALLERY_FILE
{
	private $table;
	
	function PA_GALLERY()
	{
		global $DB;
		parent::PA_GALLERY_FILE();
		
		$this->table = $DB->tables->gallery;
	}
		
	function createGallery()
	{
		global $DB;
		
		if($DB->insert($this->table,array("status"=>"temporary")))
			return $DB->lastInsertId();
		else
			return false;
	}
	
	function selectGallery($galleryId)
	{
		global $DB;
		
		return $DB->get_row("SELECT * FROM {$this->table} WHERE gallery_id=?",array($galleryId));
	}
	
	function deleteGallery($galleryId)
	{
		global $DB;
		
		return $DB->execute("DELETE FROM {$this->table} WHERE gallery_id=?",array($galleryId)) &&
				$this->deleteGalleryFilesByGalleryId($galleryId);
	}
	
}