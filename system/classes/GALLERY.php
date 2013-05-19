<?php
class PA_GALLERY extends PA_GALLERY_FILE
{
	private $table;
	
	function PA_GALLERY()
	{
		parent::PA_GALLERY_FILE();
		
		$this->table = $this->tables->gallery;
	}
		
	function createGallery(){
		return $this->insert($this->table,array("status"=>"temporary"));
	}
	
	function selectGallery($gallery_id){
		return $this->get_row("SELECT * FROM {$this->table} WHERE gallery_id=?",array($gallery_id));
	}

	function deleteGallery($gallery_id){
		return $this->deleteWholeFilesFromGallery($gallery_id) &&
                $this->execute("DELETE FROM {$this->table} WHERE gallery_id=?",array($gallery_id));
	}
}