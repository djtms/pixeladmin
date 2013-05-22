<?php
class PA_UPLOADER extends DB{
	public $table;
	public $error = "";
	public $copyNameTag = "Kopya";

    private $public_root;
    private $private_root;
	
	function PA_UPLOADER(){
		parent::DB();
		
		$this->table = $this->tables->file;

        global $public_uploadurl;
        global $private_uploadurl;

        $this->public_root = $public_uploadurl;
        $this->private_root = $private_uploadurl;
	}
	
	function uploadFile($directory_id, $file=null, $rename = null, $access_type = "public"){
		global $ADMIN;
	    $root = $this->{$access_type . "_root"};

        $isStandartUpload = true;
        $filename = $file["name"];
        $temporary_file = "";

        if(!is_array($file)){
            $isStandartUpload = false;

            $fileInfo = (object)pathinfo($file);
            $extension = strtolower($fileInfo->extension);
            $filename = $fileInfo->basename;

            if(preg_match("/^((http\:)|(https\:)|(\/\/))/", $file) && ($remote_file = file_get_contents($file))){
                // gecici dosya ismini hesapla ---------------------------------------
                $temporary_file = "temp_" . uniqid() . "." . $extension;

                // Gecici dosyayı olustur
                if(!file_put_contents($temporary_file, $remote_file)){
                    $this->error = "Geçici dosya oluşturulamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
                    return false;
                }
            }
        }

        if(($rename != null) && (strlen($rename) > 2)){
            $filename = $rename;
        }

		$properties = $ADMIN->FILE->calculateFileProperties($directory_id, $filename, true, true, $access_type);
		$thumb_file_id = $ADMIN->FILE->calculateThumbnailId($properties->extension);
		$resolution = (object)array("width"=>0,"height"=>0);

        if($isStandartUpload){
            if($file["error"] != 0){
                $this->error = "Upload hata kodu: " . $file["error"];
                return false;
            }
            else if(!move_uploaded_file($file["tmp_name"], $root . $properties->url)){
                $this->error = "Upload edilemedi!";
                return false;
            }
        }
        else{
            if((strlen($temporary_file) > 0) && file_exists($temporary_file) && !rename($temporary_file, ($root . $properties->url))){
                $this->error = "Geçici dosya taşınamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
                return false;
            }
            else if(!copy($file, $root . $properties->url)){
                $this->error = "Dosya kopyalanamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
                return false;
            }
        }

		if($properties->type == "image"){
			$ADMIN->IMAGE_PROCESSOR->load($root . $properties->url);
			$resolution = $ADMIN->IMAGE_PROCESSOR->getResolution();
		}
		
		if($file_id = $this->insert($this->table,array("basename"=>$properties->basename,
												"filename"=>$properties->filename,
												"directory_id"=>$properties->directory_id,
												"url"=>$properties->url,
												"type"=>$properties->type,
												"extension"=>$properties->extension,
												"size"=>$properties->size,
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