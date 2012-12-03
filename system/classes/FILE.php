<?php

class PA_FILE extends PA_THUMB
{
	private $table;
	
	function PA_FILE()
	{
		parent::PA_THUMB();
		
		$this->table = $this->tables->file;
	}
	
	public function selectSystemFileByFilename($filename)
	{
		global $systemurl;
		
		return $this->get_value("SELECT CONCAT('{$systemurl}',url) AS url FROM {$this->table} WHERE filename=? AND access_type='system'",array($filename));
	}
	
	public function listFilesByDirectory($directory_id)
	{
		global $uploadurl;
		
		$query  = "SELECT *,CONCAT('{$uploadurl}',url) AS url FROM {$this->table} WHERE directory_id=? AND access_type='public'";
		
		return $this->get_rows($query,array($directory_id));
	}
	
	public function selectFileById($file_id)
	{
		global $uploadurl;
		global $common_admin_site;
		$full_upload_url = $uploadurl . $common_admin_site;
		
		return $this->get_row("SELECT *,CONCAT('{$full_upload_url}',url) AS url FROM {$this->table} WHERE file_id=?",array($file_id));
	}
	
	public function selectFileUrlById($file_id)
	{
		global $uploadurl;
		global $common_admin_site;
		$full_upload_url = $uploadurl . $common_admin_site;
		
		return $this->get_value("SELECT CONCAT('{$full_upload_url}',url) AS url FROM {$this->table} WHERE file_id=?",array($file_id));
	}
	
	public function checkFileExists($fileurl)
	{
		return $this->get_value("SELECT file_id FROM {$this->table} WHERE url=?",array($fileurl));
	}
	
	public function updateFileInfo($file_id, $basename, $filename, $thumb_file_id)
	{
		global $uploadurl;
		
		$oldFileInfo = $this->selectFileById($file_id);
		$last_update_time = currentDateTime();
		$url = $this->get_value("SELECT directory FROM {$this->tables->directory} WHERE directory_id=?", array($oldFileInfo->directory_id)) . $basename;
		$full_url = $uploadurl . $url;
		
		if(rename($oldFileInfo->url, $full_url)) // Dosya ismini güncelle
		{
			// dosya bilgilerini database de de güncelle
			return $this->execute("UPDATE {$this->table} SET basename=?, filename=?, url=?, thumb_file_id=?, last_update_time=? WHERE file_id=?", array($basename, $filename, $url, $thumb_file_id, $last_update_time, $file_id));	
		}
		else
			return false;
	}
	
	public function deleteFile($file_id)
	{
		$file = $this->selectFileById($file_id);
		
		// dosya mevcutsa sil
		if(file_exists($file->url))
			unlink($file->url);
		
		// dosyanın thumbnaillerini sil
		if(!$this->deleteFileThumbs($file_id))
			return false;
		
		// dosya bilgilerini database den sil
		return $this->execute("DELETE FROM {$this->table} WHERE file_id=?",array($file_id));
	}
}