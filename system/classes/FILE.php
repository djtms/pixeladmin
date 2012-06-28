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
	
	public function listFilesByDirectory($directory)
	{
		global $uploadurl;
		
		$query  = "SELECT *,CONCAT('{$uploadurl}',url) AS url FROM {$this->table} WHERE directory=? AND access_type='public'";
		
		return $this->get_rows($query,array($directory));
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
	
	public function selectFileIdByUrl($url)
	{
		return $this->get_value("SELECT file_id FROM {$this->table} WHERE url=? ",array($url));
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
		$url = $oldFileInfo->directory . $basename;
		$full_url = $uploadurl . $url;
		
		if(rename($oldFileInfo->url, $full_url))
		{
			return $this->execute("UPDATE {$this->table} SET basename=?, filename=?, url=?, thumb_file_id=?, last_update_time=? WHERE file_id=?", array($basename, $filename, $url, $thumb_file_id, $last_update_time, $file_id));	
		}
		else
			return false;
	}
	
	public function deleteFileByUrl($fileurl)
	{
		global $uploadurl;
		
		$fileurl = preg_replace("/^" . preg_quote($uploadurl,"/") . "/", "", $fileurl);
		$fullpath = $uploadurl . $fileurl;
		
		if(is_dir($fullpath))
			return false;
		else if(file_exists($fullpath))
			unlink($fullpath);
		
		
		if(!$this->deleteFileThumbs($this->selectFileIdByUrl($fileurl)))
			return false;
		else if(!is_dir($fullpath))
			return $this->execute("DELETE FROM {$this->table} WHERE url=?",array($fileurl));
	}
	
	public function deleteFileById($file_id)
	{
		if($file_id > 0)
		{
			$file = $this->selectFileById($file_id);
			return $this->deleteFileByUrl($file->url);
		}
		else
			return true;
	}
}