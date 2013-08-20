<?php

class PA_FILE extends PA_THUMB
{
	private $table;
    private $table_directory;
    private $public_root;
    private $private_root;
    private $copyNameTag = "Kopyasi";
	
	function PA_FILE(){
		parent::PA_THUMB();
		
		$this->table = $this->tables->file;
        $this->table_directory = $this->tables->directory;

        global $public_uploadurl;
        global $private_uploadurl;

        $this->public_root = $public_uploadurl;
        $this->private_root = $private_uploadurl;
	}

    /**
     * upload/files/ dizininde herhangi bir yerde bulunan bir dosyanın bilgilerini sadece url bilgisini kullanarak database'e kaydeder. Önemli not, dosya belirtilen adreste var olmalı
     * @param $file_path dosya adresi, örnek: sample/files/myFile.jpg
     * @return bool
     */
    public function saveFileInfoToDbByPath($file_path, $access_type="public"){
        $root = $this->{$access_type . "_root"};

        $directory = trim(dirname($file_path));
        $directory = preg_replace("/^\/?(.*?)\/?$/","$1", $directory) . "/";
        $directory = preg_replace("/^" . preg_quote($root , "/") . "/", "", $directory);

        if(strlen($directory) <= 0){
            $directory_id = -1;
        }
        else if($dir = $this->get_row("SELECT * FROM {$this->table_directory} WHERE directory=? AND access_type=?", array($directory, $access_type))){
            $directory_id = $dir->directory_id;
        }
        else{
            $this->error[] = "* Girilen dosya dizini bulunamadı!";
            return false;
        }

        $properties = $this->calculateFileProperties($directory_id, $file_path, false, false, $access_type);

        if(!file_exists($root . $properties->url)){
            $this->error[] = "* Dosya bulunamadı!";
            return false;
        }
        else if($file_id = $this->getFileIdByUrl($properties->url)){
            return $file_id;
        }
        else{
            return $this->insert($this->table, (array)$properties);
        }
    }

    /**
     * upload/files/ dizinindeki tüm dosyaları veritabanı ile senkronize hale getirir, kaydı olmayan dosyaları
     * veritabanına ekler, kaydı olupta kendisi olmayan dosyaların bilgilerini veritabanından siler
     * @return bool
     */
    function syncronizeFiles($access_type = "public"){
        $root = $this->{$access_type . "_root"};

        // tüm dizinlerdeki dosyaları tara ve database'de kaydı olmayanları kaydet
        $directories = $this->get_rows("SELECT * FROM {$this->table_directory} WHERE access_type=?", array($access_type));
        $directories[] = (object)array("directory"=>""); // ana dizinide ekle

        foreach($directories as $d){
            $directory = $root . $d->directory;
            $sub_files = scandir($directory);

            foreach($sub_files as $sf){
                if(($sf != ".") && ($sf != "..") && !is_dir($directory . $sf) && file_exists($directory . $sf)){
                    $this->saveFileInfoToDbByPath(($directory . $sf), $access_type);
                }
            }
        }

        // tum dosyalari tara, var olmayan dosyalari database'den sil.
        $files = $this->get_rows("SELECT * FROM {$this->table} WHERE access_type=?", array($access_type));
        foreach($files as $f){
            if(!file_exists($root . $f->url)){
                $this->execute("DELETE FROM {$this->table} WHERE file_id=?", array($f->file_id));
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
    function calculateFileProperties($directory_id, $file_path, $fix_filename = true, $generate_duplicated_name = true, $access_type="public"){
        $file_name = basename($file_path);
        $creation_time = currentDateTime();
        if($fix_filename){
            $file_name = fixStringForWeb($file_name);
        }

        if(trim($file_name) != ""){
            if($generate_duplicated_name){
                $copied_file_id = $this->getDuplicatedFileId($directory_id, $file_name, $access_type);
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

                if(($type == "image") && ($size > 0)){
                    global $ADMIN;

                    $ADMIN->IMAGE_PROCESSOR->load($file_path);
                    $resolution = $ADMIN->IMAGE_PROCESSOR->getResolution();
                }
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
                                "access_type"=>$access_type);
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
    public function listFilesByDirectory($directory_id, $access_type="public"){
		$root = $this->{$access_type . "_root"};

		return $this->get_rows("SELECT *, CONCAT('{$root}',url) AS url FROM {$this->table} WHERE directory_id=? AND access_type=? ORDER BY filename ASC", array($directory_id, $access_type));
	}

    /**
     * İstenen file_id'ye sahip dosyanın bilgilerini döndürür
     * @param $file_id
     * @return mixed
     */
    public function selectFileById($file_id){
        if($file = $this->get_row("SELECT * FROM {$this->table} WHERE file_id=?",array($file_id))){
            $root = $this->{$file->access_type . "_root"};
            $file->url = $root . $file->url;

            return $file;
        }
        else{
            return false;
        }
    }

    /**
     * istenen file_id'ye sahip dosyanın url'ini döndürür
     * @param $file_id
     * @return strings
     */
    public function selectFileUrlById($file_id){
		if($file = $this->selectFileById($file_id)){
            return $file->url;
        }
        else{
            return false;
        }
	}

    /**
     * adresi verilen dosyanın veritabanında kaydının olup olmadığını kontrol eder, dönüş olarak file_id değerini gönderir
     * @param $fileurl
     * @return strings
     */
    public function getFileIdByUrl($url, $access_type = "public"){
		return $this->get_value("SELECT file_id FROM {$this->table} WHERE url=? AND access_type=?", array($url, $access_type));
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
		if($file = $this->selectFileById($file_id)){
            $root = $this->{$file->access_type . "_root"};
            $last_update_time = currentDateTime();
            $new_url = $this->get_value("SELECT directory FROM {$this->table_directory} WHERE directory_id=?", array($file->directory_id)) . $basename;
            $new_url_full_path = $root . $new_url;

            // Dosya ismini güncelle ve database bilgilerini guncelle
            if(rename($file->url, $new_url_full_path)){
                return $this->execute("UPDATE {$this->table} SET basename=?, filename=?, url=?, thumb_file_id=?, last_update_time=? WHERE file_id=?", array($basename, $filename, $new_url, $thumb_file_id, $last_update_time, $file_id));
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
	}

    /**
     * İstenen file_id'ye sahip dosyanın tüm bilgilerini ve kendisini siler.
     * @param $file_id
     * @return bool
     */
    public function deleteFile($file_id){
		$file = $this->selectFileById($file_id);
		
		// dosya mevcutsa sil
		if(file_exists($file->url)){
			unlink($file->url);
        }

		// dosyanın thumbnaillerini sil
		if(!$this->deleteFileThumbs($file_id)){
			return false;
        }

		// dosya bilgilerini database den sil
		return $this->execute("DELETE FROM {$this->table} WHERE file_id=?", array($file_id));
	}


    /**
     * istenen extension'a göre dosyanın thumbnail olarak kullanacağı dosyanın file_id'sini döndürür
     * @param $extension
     * @return int|strings
     */
    function calculateThumbnailId($extension){
        if(preg_match("/jpg|jpeg|png|gif$/i", $extension))
            return -1;
        else{
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
    private function getDuplicatedFileId($directory_id, $basename, $access_type="public"){
        $file_id = $this->get_value("SELECT file_id FROM {$this->table} WHERE directory_id=? AND basename=? AND access_type=?",array($directory_id, $basename, $access_type));

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
        if($copied_file_id <= 0){
            return false;
        }

        /* Kopyalanan tüm dosyaların bilgilerini al */
        $duplicatedFiles = $this->get_rows("SELECT filename FROM {$this->table} WHERE copied_file_id=?",array($copied_file_id));
        $newDuplicateFileNumber = 1;

        /* Kopyalanan dosyaların isimlerini kontrol et ve ona göre yeni kopya numarası üret */
        if(sizeof($duplicatedFiles) > 0){
            $duplicateNumbers = array();
            foreach($duplicatedFiles as $df){
                if(preg_match("/\s" . preg_quote($this->copyNameTag,"/") . "\s\([0-9]+\)$/", $df->filename,$match)){
                    $duplicateNumbers[] = preg_replace("/" . preg_quote($this->copyNameTag,"/") . "|\(|\)/", "", $match[0]);
                }
            }

            if(sizeof($duplicateNumbers) > 0){
                sort($duplicateNumbers,SORT_NUMERIC);
                $newDuplicateFileNumber = $duplicateNumbers[sizeof($duplicateNumbers) - 1];
                $newDuplicateFileNumber++;
            }
        }

        /* Kopyası alınan dosyanın adını ve uzantısını al*/
        $copiedFile = $this->get_row("SELECT filename,extension FROM {$this->table} WHERE file_id=?", array($copied_file_id));

        /* Yeni kopya ismini üret ve döndür */
        return "$copiedFile->filename $this->copyNameTag ($newDuplicateFileNumber).$copiedFile->extension";
    }
}