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
	
	function uploadFile($directory_id, $file=null, $access_type = "public"){
		global $ADMIN;
		global $uploadurl;
		
		$size = $file["size"];
		$properties = $ADMIN->FILE->calculateFileProperties($directory_id, $file["name"]);
		$thumb_file_id = $ADMIN->FILE->calculateThumbnailId($properties->extension);
		$resolution = (object)array("width"=>0,"height"=>0);
		
		if($file["error"] != 0){
			$this->error = "Upload hata kodu: " . $file["error"];
			return false;
		}
		else if(!move_uploaded_file($file["tmp_name"], $uploadurl . $properties->url)){
			$this->error = "Upload edilemedi!";
			return false;
		}
		
		if($properties->type == "image"){
			$ADMIN->IMAGE_PROCESSOR->load($uploadurl . $properties->url);
			$resolution = $ADMIN->IMAGE_PROCESSOR->getResolution();
		}
		
		if($file_id = $this->insert($this->table,array("basename"=>$properties->basename,
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
            return $file_id;
		}
		else{
            $this->error = "Database'e kaydedilemedi!";
            return false;
		}
	}
	

}