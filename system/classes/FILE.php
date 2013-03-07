<?php

class PA_FILE extends PA_THUMB
{
	private $table;
	
	function PA_FILE()
	{
		parent::PA_THUMB();
		
		$this->table = $this->tables->file;
	}

    /**
     * upload/files/ dizininde herhangi bir yerde bulunan bir dosyanın bilgilerini sadece url bilgisini kullanarak database'e kaydeder. Önemli not, dosya belirtilen adreste var olmalı
     * @param $file_path dosya adresi, örnek: sample/files/myFile.jpg
     * @return bool
     */
    public function saveFileInfoToDbByPath($file_path){
        global $ADMIN;
        global $uploadurl;

        $directory = trim(dirname($file_path));
        $directory = preg_replace("/^\/?(.*?)\/?$/","$1", $directory) . "/";
        $directory = preg_replace("/^" . preg_quote($uploadurl , "/") . "/", "", $directory);

        if(strlen($directory) <= 0){
            $directory_id = -1;
        }
        else if($dir = $ADMIN->DB->get_row("SELECT * FROM {$ADMIN->DB->tables->directory} WHERE directory=?", array($directory))){
            $directory_id = $dir->directory_id;
        }
        else{
            $this->error[] = "* Girilen adres bulunamadı!";
            return false;
        }

        $properties = $this->calculateFileProperties($directory_id, $file_path, false, false);

        if(!file_exists($uploadurl . $properties->url)){
            $this->error[] = "* Dosya bulunamadı!";
            return false;
        }
        else if(!$this->checkFileExists($properties->url)){
            return $this->insert($this->table, (array)$properties);
        }
        else{
            return true;
        }
    }

    /**
     * upload/files/ dizinindeki tüm dosyaları veritabanı ile senkronize hale getirir, kaydı olmayan dosyaları
     * veritabanına ekler, kaydı olupta kendisi olmayan dosyaların bilgilerini veritabanından siler
     * @return bool
     */
    function syncronizeFiles(){
        global $ADMIN;
        global $uploadurl;

        $table_directory = $ADMIN->DB->tables->directory;
        $table_file = $ADMIN->DB->tables->file;

        // tüm dizinlerdeki dosyaları tara ve database'de kaydı olmayanları kaydet
        $directories = $ADMIN->DB->get_rows("SELECT * FROM {$table_directory}");
        $directories[] = (object)array("directory"=>""); // ana dizinide ekle

        foreach($directories as $d){
            $directory = $uploadurl . $d->directory;
            $subfiles = scandir($directory);

            foreach($subfiles as $sf){
                if(($sf != ".") && ($sf != "..") && !is_dir($directory . $sf) && file_exists($directory . $sf)){
                    $this->saveFileInfoToDbByPath($directory . $sf);
                }
            }
        }

        // tüm dosyaları tara, var olmayan dosyaları databse'den sil.
        $files = $ADMIN->DB->get_rows("SELECT * FROM {$table_file} WHERE access_type='public'");
        foreach($files as $f){
            if(!file_exists($uploadurl . $f->url)){
                $ADMIN->DB->execute("DELETE FROM {$table_file} WHERE file_id=?", array($f->file_id));
            }
        }

        return true;
    }

    /**
     * dosya bilgilerini veritabanına uygun şekilde hesaplayın array olarak döndürür.
     * @param $directory_id
     * @param $file_path
     * @param bool $fix_filename
     * @param bool $generate_duplicated_name
     * @return bool|array
     */
    function calculateFileProperties($directory_id, $file_path, $fix_filename = true, $generate_duplicated_name = true){
        global $ADMIN;

        $file_name = basename($file_path);
        $creation_time = currentDateTime();
        if($fix_filename){
            $file_name = fixStringForWeb($file_name);
        }

        if(trim($file_name) != ""){
            if($generate_duplicated_name){
                $copied_file_id = $this->getDuplicatedFileId($directory_id, $file_name);
            }
            else{
                $copied_file_id = -1;
            }

            if($copied_file_id > 0)
                $basename = $this->generateDuplicatedName($copied_file_id);
            else
                $basename = basename($file_name);

            $pInfo = (object)pathinfo($basename);
            $extension = strtolower($pInfo->extension);
            $filename = basename($pInfo->basename,".$pInfo->extension"); // PHP 5.2.0 sürümü öncesinde pathinfo() fonksiyonu "basename" değeri üretmediği için kendimiz üretiyoruz.
            $basename = $filename . ".{$extension}";
            $type = $this->getType($basename);
            $thumb_file_id = $this->calculateThumbnailId($extension);
            $url = $this->get_value("SELECT directory FROM {$this->tables->directory} WHERE directory_id=?", array($directory_id)) . $basename;
            $resolution = new stdClass();
            $resolution->width = 0;
            $resolution->height = 0;
            $size = 0;
            if(file_exists($file_path)){
                $size = filesize($file_path);
                //TODO: burada hata alıyorum resim yüklenmiyor
                /*if($type == "image"){
                    $ADMIN->IMAGE_PROCESSOR->load($file_path);
                    $resolution = $ADMIN->IMAGE_PROCESSOR->getResolution();
                }
                */
            }


            return (object)array("basename"=>$basename,
                                "filename"=>$filename,
                                "directory_id"=>$directory_id,
                                "url"=>$url,
                                "type"=>$type,
                                "extension"=>$extension,
                                "size"=>$size,
                                "creation_time"=>$creation_time,
                                "last_update_time"=>$creation_time,
                                "width"=>$resolution->width,
                                "height"=>$resolution->height,
                                "thumb_file_id"=>$thumb_file_id,
                                "copied_file_id"=>$copied_file_id,
                                "access_type"=>"public");
        }
        else
            return false;
    }


    /**
     * Sistem dosyası olarak kayıtlı olan ve istenen isimde olan  dosyanın url'ini döndürür
     * @param $filename
     * @return strings
     */
    public function selectSystemFileByFilename($filename){
		global $systemurl;
		
		return $this->get_value("SELECT CONCAT('{$systemurl}',url) AS url FROM {$this->table} WHERE filename=? AND access_type='system'",array($filename));
	}

    /**
     * İstenen directory_id'sine sahip dosyaları listeler
     * @param $directory_id
     * @return array
     */
    public function listFilesByDirectory($directory_id){
		global $uploadurl;
		
		$query  = "SELECT *,CONCAT('{$uploadurl}',url) AS url FROM {$this->table} WHERE directory_id=? AND access_type='public'";
		
		return $this->get_rows($query,array($directory_id));
	}

    /**
     * İstenen file_id'ye sahip dosyanın bilgilerini döndürür
     * @param $file_id
     * @return mixed
     */
    public function selectFileById($file_id)
	{
		global $uploadurl;
		global $common_admin_site;
		$full_upload_url = $uploadurl . $common_admin_site;
		
		return $this->get_row("SELECT *,CONCAT('{$full_upload_url}',url) AS url FROM {$this->table} WHERE file_id=?",array($file_id));
	}

    /**
     * istenen file_id'ye sahip dosyanın url'inin döndürür
     * @param $file_id
     * @return strings
     */
    public function selectFileUrlById($file_id){
		global $uploadurl;
		global $common_admin_site;
		$full_upload_url = $uploadurl . $common_admin_site;
		
		return $this->get_value("SELECT CONCAT('{$full_upload_url}',url) AS url FROM {$this->table} WHERE file_id=?",array($file_id));
	}

    /**
     * adresi verilen dosyanın veritabanında kaydının olup olmadığını kontrol eder, dönüş olarak file_id değerini gönderir
     * @param $fileurl
     * @return strings
     */
    public function checkFileExists($fileurl){
		return $this->get_value("SELECT file_id FROM {$this->table} WHERE url=?",array($fileurl));
	}

    /**
     * İstenen file_id'ye sahip dosyanın bilgilerini günceller
     * @param $file_id
     * @param $basename
     * @param $filename
     * @param $thumb_file_id
     * @return bool
     */
    public function updateFileInfo($file_id, $basename, $filename, $thumb_file_id){
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

    /**
     * İstenen file_id'ye sahip dosyanın tüm bilgilerini ve kendisini siler.
     * @param $file_id
     * @return bool
     */
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


    /**
     * istenen extension'a göre dosyanın thumbnail olarak kullanacağı dosyanın file_id'sini döndürür
     * @param $extension
     * @return int|strings
     */
    function calculateThumbnailId($extension){
        if(preg_match("/jpg|jpeg|png|gif$/i", $extension))
            return -1;
        else
        {
            if($file_id = $this->get_value("SELECT file_id FROM {$this->table} WHERE filename=? AND access_type='system'",array($extension)))
                return $file_id;
            else
                return $this->get_value("SELECT file_id FROM {$this->table} WHERE filename='generic' AND access_type='system'");
        }
    }

    // PRIVATE

    /**
     * Belirtilen dizin ve dosya ismine göre eğer aynı isimde dosya varsa, orjinal dosyanın id'sini döndürür
     * @param $directory_id
     * @param $basename
     * @return int|strings
     */
    private function getDuplicatedFileId($directory_id, $basename){
        $file_id = $this->get_value("SELECT file_id FROM {$this->table} WHERE directory_id=? AND basename=?",array($directory_id, $basename));

        return $file_id > 0 ? $file_id : -1;
    }

    private function getType($basename){
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
    private function generateDuplicatedName($copied_file_id){
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