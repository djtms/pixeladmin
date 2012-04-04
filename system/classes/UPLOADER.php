<?php
class PA_UPLOADER
{
	public $table;
	public $error = "";
	public $copyNameTag = "Kopya";
	
	function PA_UPLOADER()
	{
		global $DB;
		
		$this->table = $DB->tables->file;
	}
	
	function uploadFile($directory,$file=null, $access_type = "public")
	{
		global $ADMIN;
		global $uploadurl;
		
		$size = $file["size"];
		$properties = $this->calculateFileProperties($directory,$file["name"]);
		$thumb_file_id = $this->calculateThumbnailFileId($properties->extension);
		$uploadError = $file["error"];
		$resolution = (object)array("width"=>0,"height"=>0);
		
		if($uploadError != 0)
		{
			$this->error = "Upload Hata Kodu: $uploadError";
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
		
		if(!$ADMIN->DB->insert($this->table,array("basename"=>$properties->basename,
												"filename"=>$properties->filename,
												"directory"=>$properties->directory,
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
			return $ADMIN->DB->lastInsertId();
		}
	}
	
	function calculateThumbnailFileId($extension)
	{
		if(preg_match("/jpg|jpeg|png|gif$/i", $extension))
			return -1;
		else
		{
			global $DB;
			
			if($fileId = $DB->get_value("SELECT file_id FROM {$this->table} WHERE filename=? AND access_type='system'",array($extension)))
			{
				return $fileId;
			}
			else
				return $DB->get_value("SELECT file_id FROM {$this->table} WHERE filename='generic' AND access_type='system'");
		}
	}
	
	function calculateFileProperties($directory,$fileName)
	{
		$file = array();
		$creation_time = currentDateTime();
		$directory = trim($directory);
		$fileName = fixStringForWeb($fileName);
		
		if(trim($fileName) != "")
		{
			$copied_file_id = $this->getCopiedFileId($directory,basename($fileName));
			
			if($copied_file_id > 0)
				$basename = $this->generateDuplicatedName($copied_file_id);
			else
				$basename = basename($fileName);
			
			$pInfo = (object)pathinfo($basename);
			$extension = strtolower($pInfo->extension);
			$filename = basename($pInfo->basename,".$pInfo->extension"); // PHP 5.2.0 sürümü öncesinde pathinfo() fonksiyonu "basename" değeri üretmediği için kendimiz üretiyoruz.
			$basename = $filename . ".{$extension}";
			$type = $this->getType($basename);
			$url = $directory . $basename;
			
			return (object)array("basename"=>$basename,"filename"=>$filename,"directory"=>$directory,
				"url"=>$url,"type"=>$type,"extension"=>$extension,"size"=>0,
				"creation_time"=>$creation_time,"last_update_time"=>$creation_time,
				"width"=>0,"height"=>0,"thumb_file_id"=>-1,"copied_file_id"=>$copied_file_id,
				"access_type"=>"public");
		}
		else
			return false;
	}
	
	
	/* PRIVATE */
	private function getCopiedFileId($directory,$basename)
	{
		global $DB;
		
		$query = "SELECT file_id FROM {$this->table} WHERE url=?";
		$url = $directory . $basename;
		$fileId = $DB->get_value($query,array($url));
		
		return $fileId > 0 ? $fileId : -1;
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
		global $DB;
		
		/* Kopyalanan tüm dosyaların bilgilerini al */
		$duplicatedFiles = $DB->get_rows("SELECT filename FROM {$this->table} WHERE copied_file_id=?",array($copied_file_id));
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
		$copiedFile = $DB->get_row("SELECT filename,extension FROM {$this->table} WHERE file_id=?",array($copied_file_id));
		
		/* Yeni kopya ismini üret ve döndür */
		return "$copiedFile->filename $this->copyNameTag ($newDuplicateFileNumber).$copiedFile->extension";
	}
	
	
}