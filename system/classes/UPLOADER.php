<?php
class PA_UPLOADER extends DB
{
	public $table;
	public $error = "";
	public $copyNameTag = "Kopya";
	
	function PA_UPLOADER()
	{
		parent::DB();
		
		$this->table = $this->tables->file;
	}
	
	function uploadFile($directory_id, $file=null, $access_type = "public")
	{
		global $ADMIN;
		global $uploadurl;
		
		$size = $file["size"];
		$properties = $this->calculateFileProperties($directory_id, $file["name"]);
		$thumb_file_id = $this->calculateSystemThumbnailId($properties->extension);
		$resolution = (object)array("width"=>0,"height"=>0);
		
		if($file["error"] != 0)
		{
			$this->error = "Upload hata kodu: " . $file["error"];
			return false;
		}
		else if(!move_uploaded_file($file["tmp_name"], $uploadurl . $properties->url))
		{
			$this->error = "Upload edilemedi!";
			return false;
		}
		
		if($properties->type == "image")
		{
			$ADMIN->IMAGE_PROCESSOR->load($uploadurl . $properties->url);
			$resolution = $ADMIN->IMAGE_PROCESSOR->getResolution();
		}
		
		if(!$this->insert($this->table,array("basename"=>$properties->basename,
												"filename"=>$properties->filename,
												"directory_id"=>$properties->directory_id,
												"url"=>$properties->url,
												"type"=>$properties->type,
												"extension"=>$properties->extension,
												"size"=>$size,
												"creation_time"=>$properties->creation_time,
												"last_update_time"=>$properties->last_update_time,
												"width"=>$resolution->width,
												"height"=>$resolution->height,
												"thumb_file_id"=>$thumb_file_id,
												"copied_file_id"=>$properties->copied_file_id,
												"access_type"=>$access_type)))
		{
			$this->error = "Database e kaydedilemedi!";
			return false;
		}
		else
		{ 
			return $this->lastInsertId();
		}
	}
	
	function calculateSystemThumbnailId($extension){
		if(preg_match("/jpg|jpeg|png|gif$/i", $extension))
			return -1;
		else
		{
			if($fileId = $this->get_value("SELECT file_id FROM {$this->table} WHERE filename=? AND access_type='system'",array($extension)))
			{
				return $fileId;
			}
			else
				return $this->get_value("SELECT file_id FROM {$this->table} WHERE filename='generic' AND access_type='system'");
		}
	}
	
	function calculateFileProperties($directory_id, $file_name)
	{
		$file = array();
		$creation_time = currentDateTime();
		$file_name = fixStringForWeb($file_name);
		
		if(trim($file_name) != "")
		{
			$copied_file_id = $this->getDuplicatedFileId($directory_id,basename($file_name));
			
			if($copied_file_id > 0)
				$basename = $this->generateDuplicatedName($copied_file_id);
			else
				$basename = basename($file_name);
			
			$pInfo = (object)pathinfo($basename);
			$extension = strtolower($pInfo->extension);
			$filename = basename($pInfo->basename,".$pInfo->extension"); // PHP 5.2.0 sürümü öncesinde pathinfo() fonksiyonu "basename" değeri üretmediği için kendimiz üretiyoruz.
			$basename = $filename . ".{$extension}";
			$type = $this->getType($basename);
			$url = $this->get_value("SELECT directory FROM {$this->tables->directory} WHERE directory_id=?", array($directory_id)) . $basename;
			
			return (object)array("basename"=>$basename,"filename"=>$filename,"directory_id"=>$directory_id,
				"url"=>$url,"type"=>$type,"extension"=>$extension,"size"=>0,
				"creation_time"=>$creation_time,"last_update_time"=>$creation_time,
				"width"=>0,"height"=>0,"thumb_file_id"=>-1,"copied_file_id"=>$copied_file_id,
				"access_type"=>"public");
		}
		else
			return false;
	}
	
	
	/* PRIVATE */
	private function getDuplicatedFileId($directory_id, $basename)
	{
		$file_id = $this->get_value("SELECT file_id FROM {$this->table} WHERE directory_id=? AND basename=?",array($directory_id, $basename));
		
		return $file_id > 0 ? $file_id : -1;
	}
	
	private function getType($basename)
	{
		if(preg_match("/\.jpg|\.jpeg|\.png|\.gif$/i", $basename))
			return "image";
		else if(preg_match("/\.avi|\.mp4|\.flv|\.f4v$/i", $basename))
			return "movie";
		else if(preg_match("/\.mp3$/i", $basename))
			return "sound";
		else
			return "other";
	}
	
	/**
	 * <p>Verilen dosya idsine göre yeni bir kopya dosya ismi türetmek için kullanılır</p>
	 * @param (int) orjinal dosyanın id'si
	 * @return (string) kopyadosya ismi döndürür
	 */
	private function generateDuplicatedName($copied_file_id)
	{
		/* Kopyalanan tüm dosyaların bilgilerini al */
		$duplicatedFiles = $this->get_rows("SELECT filename FROM {$this->table} WHERE copied_file_id=?",array($copied_file_id));
		$newDuplicateFileNumber = 1;
		
		/* Kopyalanan dosyaların isimlerini kontrol et ve ona göre yeni kopya numarası üret */
		if(sizeof($duplicatedFiles) > 0)
		{
			$duplicateNumbers = array();
			foreach($duplicatedFiles as $df)
			{
				if(preg_match("/\s" . preg_quote($this->copyNameTag,"/") . "\s\([0-9]+\)$/", $df->filename,$match))
				{
					$duplicateNumbers[] = preg_replace("/" . preg_quote($this->copyNameTag,"/") . "|\(|\)/", "", $match[0]);	
				}
			}
			
			if(sizeof($duplicateNumbers) > 0)
			{
				sort($duplicateNumbers,SORT_NUMERIC);
				$newDuplicateFileNumber = $duplicateNumbers[sizeof($duplicateNumbers) - 1];
				$newDuplicateFileNumber++;
			}
		}
		
		/* Kopyası alınan dosyanın adını al*/
		$copiedFile = $this->get_row("SELECT filename,extension FROM {$this->table} WHERE file_id=?",array($copied_file_id));
		
		/* Yeni kopya ismini üret ve döndür */
		return "$copiedFile->filename $this->copyNameTag ($newDuplicateFileNumber).$copiedFile->extension";
	}
	
	
}