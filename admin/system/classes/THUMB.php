<?php

class PA_THUMB extends DB{
	public $baseDir;
	
	private $table;
	private $table_file;
	private $link_table;
    private $public_root;
    private $private_root;
    private $thumbs_root;
    private $system_root;
    public $error = array();
	
	function PA_THUMB(){
		global $common_admin_site;
		parent::DB();
		
		$this->baseDir = $common_admin_site;
		$this->table = $this->tables->thumb;
		$this->table_file = $this->tables->file;
		$this->link_table = $this->tables->file_thumb;

        global $systemurl;
        global $public_uploadurl;
        global $private_uploadurl;
        global $thumbsurl;

        $this->system_root = $systemurl;
        $this->public_root = $public_uploadurl;
        $this->private_root = $private_uploadurl;
        $this->thumbs_root = $thumbsurl;
	}
	
	
	public function getThumbUrl($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
		if($thumb = $this->getThumbInfo($file_id, $width, $height, $squeeze, $proportion, $position, $bg_color)){
            return $thumb->url;
        }
        else{
            return "";
        }
	}
	
	public function getThumbInfo($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id))){
			return false; // Dosya bulunamadığı zaman geri dön.
        }
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
        $root_path = $this->{$thumb_file->access_type . "_root"};

        // Genişlik yükseklik değerleri hesaplanmamışsa onları hesapla ve database'de güncelle
        if(($thumb_file->width <= 0) || ($thumb_file->height <= 0)){
            global $ADMIN;
            $ADMIN->IMAGE_PROCESSOR->load($root_path . $thumb_file->url);
            $res = $ADMIN->IMAGE_PROCESSOR->getResolution();
            $thumb_file->width = $res->width;
            $thumb_file->height = $res->height;

            $this->update($this->table_file, array("width"=>$res->width, "height"=>$res->height), array("file_id"=>$thumb_file->file_id));
        }

		//---------------------------------------------------------------------------------------------------------------
		// Önce Custom Crop yapılmış dosyayı ara, yok ise database'de kaydinin olup olmadigini kontrol et, kaydi varsa o
        // bilgilere göre tekrar crop yap, kaydi yoksa normal crop islemine devam et.
		//---------------------------------------------------------------------------------------------------------------
		if(!$squeeze){
			$thumb_filename = $thumb_file->file_id . "-custom_crop-" . $width . "x" . $height;
			$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
            $thumb_url = $this->thumbs_root . $thumb_basename;

			if($thumb = $this->get_row("SELECT * FROM {$this->table} WHERE basename=? AND crop_type='custom_crop'", array($thumb_basename))){
                $thumb = $this->cropImage($file_id, $thumb->crop_left, $thumb->crop_top, $thumb->crop_width, $thumb->crop_height, $width, $height);

                $thumb->url = $thumb_url;
                $file->url = $root_path . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
                $thumb->owner = $file; 	// Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
                // satırdaki seçim işleminden sonra diziye eklemem gerekiyor.

                return $thumb;
			}
		}
		
		//---------------------------------------------------------------------------------------------------------------
		// Crop edilmemişse Thumnail'i Oluştur
		//---------------------------------------------------------------------------------------------------------------

		// Thumbnail'in özelliklerini belirle;
		$width = intval($width);
		$width = round(($width > 0) ? $width : ($height * ($thumb_file->width / $thumb_file->height)));
		$height = intval($height);
		$height = round(($height > 0) ? $height : ($width / ($thumb_file->width / $thumb_file->height)));

		$position = $this->fixCropPosition($position);

		// Thumbnail'in daha önce üretilmiş olma ihtimaline karşılık arama yaparken kullanacağın,  eğer üretilmemişse üretirken kullanacağın dosya adını belirle
		$thumb_filename = $thumb_file->file_id . "-" . $width . "-" . $height . "-" . ($squeeze ? "s-" : "") .($proportion ? "p-" : "") . ( $position . "-") . $bg_color ;
		$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
        $thumb_url = $this->thumbs_root . $thumb_basename;

		// Thumbnail üretmek için kullanılacak kaynak dosyanın tam adresini belirle
        $source_url = $root_path . $thumb_file->url;

        // Thumbnail dosyasi mevcut degilse yeniden olustur.
        if(!file_exists($thumb_url)){
            // Uzak bir sunucudaki dosyayı kullanıyorsak
            if(preg_match("/^((http\:)|(https\:)|(\/\/))/", $source_url) && ($remote_file = file_get_contents($source_url))){
                // gecici dosya ismini hesapla ---------------------------------------
                $temporary_file = "temp_" . uniqid() . "." . $thumb_file->extension;

                // Gecici dosyayı olustur
                if(!file_put_contents($temporary_file, $remote_file)){
                    $this->error[] = "Geçici dosya oluşturulamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
                    return false;
                }

                $source_url = $temporary_file;
            }
            else{
                $temporary_file = "";
            }

            $this->generateThumbnailImage($source_url, $thumb_url, $width, $height, $squeeze, $proportion, $position, $bg_color);

            // Uzak sunucudaki bir dosyayi kullandiysak geceici olarak download ettigimiz dosyayi siliyoruz.
            if(strlen($temporary_file) > 0){
                unlink($temporary_file);
            }
        }

        if($thumb = $this->get_row("SELECT * FROM {$this->table} WHERE filename=?", array($thumb_filename))){
			$thumb->url = $thumb_url;
            $file->url = $root_path . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
			$thumb->owner = $file; 	// Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
									// satırdaki seçim işleminden sonra diziye eklemem gerekiyor.

			return $thumb;
		}
		else{
            $thumbData = array( "basename"=>$thumb_basename,"filename"=>$thumb_filename,"extension"=>$thumb_file->extension, "directory"=>"","url"=>$thumb_basename,	"width"=>$width,"height"=>$height,"squeeze"=>($squeeze ? 1 : -1),"proportion"=>($proportion ? 1 : -1),"crop_position"=>$position,"bg_color"=>$bg_color, "crop_type"=>"auto_crop");


            if(($thumb_id = $this->insert($this->table, $thumbData)) && $this->insert($this->link_table, array("file_id"=>$file_id, "thumb_id"=>$thumb_id))){
                $thumbData["thumb_id"] = $thumb_id;
                $thumbData["url"] = $this->thumbs_root . $thumbData["url"]; // buradaki değişkeni database den çekmeyip kendim ürettiğim için '$thumbsurl' değişkenini başa eklemem gerekiyor
                $thumbData["owner"] = $file; // Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
                                             // satırdaki insert işleminden sonra diziye eklemem gerekiyor.

                return (object)$thumbData;
            }
            else{
                return false;
            }
		}
	}
	
	public function selectThumbById($thumb_id){
		return $this->get_row("SELECT * FROM {$this->table} WHERE thumb_id=?",array($thumb_id));
	}
	
	public function deleteFileThumbs($file_id){
		global $ADMIN;

		// Delete Thumbnail File  -> Resim haricindeki dosyaların kendine ait gizli thumbnail dosyaları olabiliyor. O dosyaları silme işlemini burada yapıyoruz.
		$file = $ADMIN->FILE->selectFileById($file_id);
		$thumb_file = $ADMIN->FILE->selectFileById($file->thumb_file_id);
		
		if($thumb_file->access_type == "private"){
			$ADMIN->FILE->deleteFile($thumb_file->file_id);
		}
		/////////////////////////////////////////////////////////////////////////
		
		$query  = "SELECT * FROM {$this->link_table} AS lt ";
		$query .= "LEFT JOIN {$this->table} AS t ON lt.thumb_id=t.thumb_id ";
		$query .= "WHERE lt.file_id=?";
		
		$thumbs = $this->get_rows($query,array($file_id));

		if(sizeof($thumbs) > 0){
			foreach($thumbs as $t){
				if(file_exists($this->thumbs_root . $t->url))
					unlink($this->thumbs_root . $t->url);
				
				$this->execute("DELETE FROM {$this->table} WHERE thumb_id=?",array($t->thumb_id));
				$this->execute("DELETE FROM {$this->link_table} WHERE file_id=? AND thumb_id=?",array($file_id, $t->thumb_id));
			}
		}
		
		return true;
	}

	function cropImage($file_id, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height){
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
        $root_path = $this->{$thumb_file->access_type . "_root"};

		// Thumbnail'in özelliklerini belirle;
		$width = intval($resize_width);
		$height = intval($resize_height);
			
		// Thumbnail üretmek için kullanılacak kaynak dosyanın tam adresini belirle
		$source_url = $root_path . $thumb_file->url;
		
		// Thumbnail'in daha önce üretilmiş olma ihtimaline karşılık arama yaparken kullanacağın, 
		// eğer üretilmemişse üretirken kullanacağın dosya adını belirle
		$thumb_filename = $thumb_file->file_id . "-custom_crop-" . $width . "x" . $height;
		$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
        $thumb_url = $this->thumbs_root . $thumb_basename;

        // Thumbnail dosyasi mevcut degilse yeniden olustur.
        if(!file_exists($thumb_url)){
            // Uzak bir sunucudaki dosyayı kullanıyorsak
            if(preg_match("/^((http\:)|(https\:)|(\/\/))/", $source_url) && ($remote_file = file_get_contents($source_url))){
                // gecici dosya ismini hesapla ---------------------------------------
                $temporary_file = "temp_" . uniqid() . "." . $thumb_file->extension;

                // Gecici dosyayı olustur
                if(!file_put_contents($temporary_file, $remote_file)){
                    $this->error[] = "Geçici dosya oluşturulamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
                    return false;
                }

                $source_url = $temporary_file;
            }
            else{
                $temporary_file = "";
            }

            $this->generateCropImage($source_url, $thumb_url, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height);

            // Uzak sunucudaki bir dosyayi kullandiysak geceici olarak download ettigimiz dosyayi siliyoruz.
            if(strlen($temporary_file) > 0){
                unlink($temporary_file);
            }
        }
		
		// Thumbnail in daha önce üretilmiş olup olmadığını kontrol et
		if($thumb = $this->get_row("SELECT * FROM {$this->table} WHERE basename=? AND crop_type='custom_crop'",array($thumb_basename))){
			$thumb->url = $thumb_url;
            $file->url = $root_path . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
			$thumb->owner = $file; 	// Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
									// satırdaki seçim işleminden sonra diziye eklemem gerekiyor.

            return $thumb;
		}
		else{
			$file->url = $root_path . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor

            $thumbData = array("basename"=>$thumb_basename,"filename"=>$thumb_filename,"extension"=>$thumb_file->extension, "directory"=>"","url"=>$thumb_basename,	"width"=>$width,"height"=>$height, "crop_left"=>$left, "crop_top"=>$top, "crop_width"=>$crop_width, "crop_height"=>$crop_height,  "crop_type"=>"custom_crop");

            if(($thumb_id = $this->insert($this->table,$thumbData)) && $this->insert($this->link_table,array("file_id"=>$file_id,"thumb_id"=>$thumb_id))){
                $thumbData["thumb_id"] = $thumb_id;
                $thumbData["url"] = $this->thumbs_root . $thumbData["url"]; // buradaki değişkeni database den çekmeyip kendim ürettiğim için '$thumbsurl' değişkenini başa eklemem gerekiyor
                $thumbData["owner"] = $file; // Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
                                             // satırdaki insert işleminden sonra diziye eklemem gerekiyor.

                return (object)$thumbData;
            }
            else{
                return false;
            }
		}
	}
	
	function listCustomCroppedImages($file_id){
		$query  = "SELECT t.*,CONCAT('{$this->thumbs_root}',t.url) AS url FROM {$this->link_table} AS l ";
		$query .= "LEFT JOIN {$this->table} AS t ON l.thumb_id=t.thumb_id ";
		$query .= "WHERE t.crop_type='custom_crop' AND l.file_id=?";
		
		return $this->get_rows($query,array($file_id));
	}
	
	function getRetinaImageInfo($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
		// Retina display resimlerine gore cozunurluk ve dosya ismi duzenlemelerini yap
        $width = $width * 2;
		$height = $height * 2;
		$retinaImageNameSuffix = "@x2";
		
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
        $root_path = $this->{$thumb_file->access_type . "_root"};
		
		// Thumbnail'in özelliklerini belirle;
		$width = intval($width);
		$width = round(($width > 0) ? $width : ($height * ($thumb_file->width / $thumb_file->height)));
		$height = intval($height);
		$height = round(($height > 0) ? $height : ($width / ($thumb_file->width / $thumb_file->height)));
			
		$position = $this->fixCropPosition($position);
		
		// Thumbnail üretmek için kullanılacak kaynak dosyanın tam adresini belirle
        $source_url = $root_path . $thumb_file->url;

		// Thumbnail'in daha önce üretilmiş olma ihtimaline karşılık arama yaparken kullanacağın,
		// eğer üretilmemişse üretirken kullanacağın dosya adını belirle
		$thumb_filename = $thumb_file->file_id . "-" . $width . "-" . $height . "-" . ($squeeze ? "s-" : "") .($proportion ? "p-" : "") . ( $position . "-") . $bg_color . $retinaImageNameSuffix;
		$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
        $thumb_url = $this->thumbs_root . $thumb_basename;

        // Thumbnail dosyasi mevcut degilse yeniden olustur.
        if(!file_exists($thumb_url)){
            // Uzak bir sunucudaki dosyayı kullanıyorsak
            if(preg_match("/^((http\:)|(https\:)|(\/\/))/", $source_url) && ($remote_file = file_get_contents($source_url))){
                // gecici dosya ismini hesapla ---------------------------------------
                $temporary_file = "temp_" . uniqid() . "." . $thumb_file->extension;

                // Gecici dosyayı olustur
                if(!file_put_contents($temporary_file, $remote_file)){
                    $this->error[] = "Geçici dosya oluşturulamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
                    return false;
                }

                $source_url = $temporary_file;
            }
            else{
                $temporary_file = "";
            }

            $this->generateThumbnailImage($source_url, $thumb_url, $width, $height, $squeeze, $proportion, $position, $bg_color);

            // Uzak sunucudaki bir dosyayi kullandiysak geceici olarak download ettigimiz dosyayi siliyoruz.
            if(strlen($temporary_file) > 0){
                unlink($temporary_file);
            }
        }

        if($thumb = $this->get_row("SELECT * FROM {$this->table} WHERE filename=?", array($thumb_filename))){
            $thumb->url = $thumb_url;
            $file->url = $root_path . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
            $thumb->owner = $file; 	// Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
            // satırdaki seçim işleminden sonra diziye eklemem gerekiyor.

            return $thumb;
        }
        else{
            $thumbData = array( "basename"=>$thumb_basename,"filename"=>$thumb_filename,"extension"=>$thumb_file->extension, "directory"=>"","url"=>$thumb_basename,	"width"=>$width,"height"=>$height,"squeeze"=>($squeeze ? 1 : -1), "proportion"=>($proportion ? 1 : -1),"crop_position"=>$position,"bg_color"=>$bg_color, "crop_type"=>"auto_crop");


            if(($thumb_id = $this->insert($this->table, $thumbData)) && $this->insert($this->link_table, array("file_id"=>$file_id, "thumb_id"=>$thumb_id))){
                $thumbData["thumb_id"] = $thumb_id;
                $thumbData["url"] = $this->thumbs_root . $thumbData["url"]; // buradaki değişkeni database den çekmeyip kendim ürettiğim için '$thumbsurl' değişkenini başa eklemem gerekiyor
                $thumbData["owner"] = $file; // Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
                // satırdaki insert işleminden sonra diziye eklemem gerekiyor.

                return (object)$thumbData;
            }
            else{
                return false;
            }
        }
	}
	
	function getMaskedImageInfo($mask_image_path, $file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
		$maskedImageNameSuffix = "_masked";
	
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
        $root_path = $this->{$thumb_file->access_type . "_root"};
	
		// Thumbnail'in özelliklerini belirle;
		$width = intval($width);
		$width = round(($width > 0) ? $width : ($height * ($thumb_file->width / $thumb_file->height)));
		$height = intval($height);
		$height = round(($height > 0) ? $height : ($width / ($thumb_file->width / $thumb_file->height)));
			
		$position = $this->fixCropPosition($position);

        // Thumbnail üretmek için kullanılacak kaynak dosyanın tam adresini belirle
		$source_url = $root_path . $thumb_file->url;

		// Thumbnail'in daha önce üretilmiş olma ihtimaline karşılık arama yaparken kullanacağın,
		// eğer üretilmemişse üretirken kullanacağın dosya adını belirle
		$thumb_filename = $thumb_file->file_id . "-" . $width . "-" . $height . "-" . ($squeeze ? "s-" : "") .($proportion ? "p-" : "") . ( $position . "-") . $bg_color . $maskedImageNameSuffix;
		$thumb_basename = $thumb_filename . ".png";
        $thumb_url = $this->thumbs_root . $thumb_basename;


        // Thumbnail dosyasi mevcut degilse yeniden olustur.
        if(!file_exists($thumb_url)){
            // Uzak bir sunucudaki dosyayı kullanıyorsak
            if(preg_match("/^((http\:)|(https\:)|(\/\/))/", $source_url) && ($remote_file = file_get_contents($source_url))){
                // gecici dosya ismini hesapla ---------------------------------------
                $temporary_file = "temp_" . uniqid() . "." . $thumb_file->extension;

                // Gecici dosyayı olustur
                if(!file_put_contents($temporary_file, $remote_file)){
                    $this->error[] = "Geçici dosya oluşturulamadı! Dosya: " . __FILE__ . " Satır: " . __LINE__;
                    return false;
                }

                $source_url = $temporary_file;
            }
            else{
                $temporary_file = "";
            }

            $this->generateMaskedImage($mask_image_path, $source_url, $thumb_url, $width, $height, $squeeze, $proportion, $position, $bg_color);

            // Uzak sunucudaki bir dosyayi kullandiysak geceici olarak download ettigimiz dosyayi siliyoruz.
            if(strlen($temporary_file) > 0){
                unlink($temporary_file);
            }
        }


        if($thumb = $this->get_row("SELECT * FROM {$this->table} WHERE filename=?", array($thumb_filename))){
            $thumb->url = $thumb_url;
            $file->url = $root_path . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
            $thumb->owner = $file; 	// Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
            // satırdaki seçim işleminden sonra diziye eklemem gerekiyor.

            return $thumb;
        }
        else{
            $thumbData = array( "basename"=>$thumb_basename,"filename"=>$thumb_filename,"extension"=>$thumb_file->extension, "directory"=>"","url"=>$thumb_basename,	"width"=>$width,"height"=>$height,"squeeze"=>($squeeze ? 1 : -1), "proportion"=>($proportion ? 1 : -1),"crop_position"=>$position,"bg_color"=>$bg_color, "crop_type"=>"auto_crop");

            if(($thumb_id = $this->insert($this->table, $thumbData)) && $this->insert($this->link_table, array("file_id"=>$file_id, "thumb_id"=>$thumb_id))){
                $thumbData["thumb_id"] = $thumb_id;
                $thumbData["url"] = $this->thumbs_root . $thumbData["url"]; // buradaki değişkeni database den çekmeyip kendim ürettiğim için '$thumbsurl' değişkenini başa eklemem gerekiyor
                $thumbData["owner"] = $file; // Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki satırdaki insert işleminden sonra diziye eklemem gerekiyor.

                return (object)$thumbData;
            }
            else{
                return false;
            }
        }
	}
	
	//--------------------------------------------------------------------------------------------------------------------------------------------------
	// PRIVATE FUNCTIONS
	//--------------------------------------------------------------------------------------------------------------------------------------------------
	private function generateThumbnailImage($existing_file_url, $target_url, $width, $height, $squeeze = false, $proportion = true, $position = "center top" ,$bg_color = "FFFFFF"){
		global $ADMIN;
		
		if(!file_exists($target_url)){
			$ADMIN->IMAGE_PROCESSOR->load($existing_file_url);
			if($squeeze){
				$ADMIN->IMAGE_PROCESSOR->resize($width, $height, $proportion, $position, $bg_color);
			}
			else{
				$ADMIN->IMAGE_PROCESSOR->autoCrop($width, $height, $position);
			}
			
			return $ADMIN->IMAGE_PROCESSOR->save($target_url);
		}
		else
			return true;
	}
	
	private function generateMaskedImage($mask_image_path, $existing_file_url, $target_url, $width, $height, $squeeze = false, $proportion = true, $position = "center top" ,$bg_color = "FFFFFF"){
		global $ADMIN;
		
		if(!file_exists($target_url)){
			$ADMIN->IMAGE_PROCESSOR->load($existing_file_url);
			if($squeeze){
				$ADMIN->IMAGE_PROCESSOR->resize($width, $height, $proportion, $position, $bg_color);
			}
			else{
				$ADMIN->IMAGE_PROCESSOR->autoCrop($width, $height, $position);
			}
			
			$ADMIN->IMAGE_PROCESSOR->mask($mask_image_path);
				
			return $ADMIN->IMAGE_PROCESSOR->save($target_url);
		}
		else
			return true;
	}
	
	private function generateCropImage($source_url, $target_url, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height){
		global $ADMIN;
		return $ADMIN->IMAGE_PROCESSOR->load($source_url) &&
		$ADMIN->IMAGE_PROCESSOR->crop($crop_width, $crop_height, $left, $top) &&
		$ADMIN->IMAGE_PROCESSOR->resize($resize_width, $resize_height, true) &&
		$ADMIN->IMAGE_PROCESSOR->save($target_url);
	}
	
	private function fixCropPosition($position){
		$X = "left";
		$Y = "top";
		
		if(($position == "") || ($position == null) || !(preg_match("/[\s]+/", trim($position)))){
			return $X . "_" . $Y;
		}
		
		$position = strtolower($position);
		$position = trim($position);
		$position = preg_replace("/[\s]+/", " ", $position);
		$position_array = explode(" ", $position);
		
		
		if($position_array[0] == "right")
			$X = "right";
		else if($position_array[0] == "center")
			$X = "center";
			
		if($position_array[1] == "bottom")
			$Y = "bottom";
		else if($position_array[1] == "center")
			$Y = "center";
			
		return $X . "_" . $Y;
	}
}