<?php

class PA_FILE extends PA_THUMB {

    private $table;
    private $table_directory;
    private $public_root;
    private $private_root;
    private $copyNameTag = "Kopyasi";

    function PA_FILE() {
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
    public function saveFileInfoToDbByPath($file_path, $access_type = "public") {
        $root = $this->{$access_type . "_root"};

        $directory = trim(dirname($file_path));
        $directory = preg_replace("/^\/?(.*?)\/?$/", "$1", $directory) . "/";
        $directory = preg_replace("/^" . preg_quote($root, "/") . "/", "", $directory);

        if (strlen($directory) <= 0) {
            $directory_id = -1;
        } else if ($dir = $this->get_row("SELECT * FROM {$this->table_directory} WHERE directory=? AND access_type=?", array($directory, $access_type))) {
            $directory_id = $dir->directory_id;
        } else {
            $this->error[] = "* Girilen dosya dizini bulunamadı!";
            return false;
        }

        $properties = $this->calculateFileProperties($directory_id, $file_path, false, false, $access_type);

        if (!file_exists($root . $properties->url)) {
            $this->error[] = "* Dosya bulunamadı!";
            return false;
        } else if ($file_id = $this->getFileIdByUrl($properties->url)) {
            return $file_id;
        } else {
            return $this->insert($this->table, (array) $properties);
        }
    }

    /**
     * upload/files/ dizinindeki tüm dosyaları veritabanı ile senkronize hale getirir, kaydı olmayan dosyaları
     * veritabanına ekler, kaydı olupta kendisi olmayan dosyaların bilgilerini veritabanından siler
     * @return bool
     */
    function syncronizeFiles($directory_id = -1) {
        global $ADMIN;
        $root = $this->public_root;

        // tüm dizinlerdeki dosyaları tara ve database'de kaydı olmayanları kaydet
        if(!$directories = $ADMIN->DIRECTORY->listWholeSubDirectoriesInDB($directory_id)){
            $directories = array();
        }

        // ana dizinide ekle
        $directories[] =  (object) array("directory" => $root);

        foreach ($directories as $d) {
            $sub_files = scandir($d->directory);

            foreach ($sub_files as $sf) {
                if (!preg_match('/^\./', $sf) && !is_dir($d->directory . $sf) && file_exists($d->directory . $sf)) {
                    $this->saveFileInfoToDbByPath(($d->directory . $sf), 'public');
                }
            }
        }

        // tum dosyalari tara, var olmayan dosyalari database'den sil.
        $files = $this->get_rows("SELECT * FROM {$this->table} WHERE access_type=?", array('public'));
        foreach ($files as $f) {
            if (!file_exists($root . $f->url)) {
                $this->execute("DELETE FROM {$this->table} WHERE file_id=?", array($f->file_id));
            }
        }

        return true;
    }

    /**
     * dosya bilgilerini veritabanına uygun şekilde hesaplayın array olarak döndürür.
     * @param $directory_id
     * @param $file_path dosyanin tam adresi veya sadece adi
     * @param bool $fix_filename
     * @param bool $generate_duplicated_name
     * @return bool|array
     */
    function calculateFileProperties($directory_id, $file_path, $fix_filename = true, $generate_duplicated_name = true, $access_type = "public") {
        $basename = basename($file_path);
        $creation_time = currentDateTime();
        $copied_file_id = -1;

        if ($fix_filename) {
            $basename = fixStringForWeb($basename);
        }

        if (trim($basename) != "") {
            if ($generate_duplicated_name) {
                $pInfo = (object) pathinfo($basename);

                // TODO: Burada kullanabilirsen parametreleri query icinde direk baglamak yerine pdo'ya uygun sekilde bagla
                $similar_file_amount = $this->get_value("SELECT COUNT(*) FROM {$this->tables->file} WHERE directory_id=? AND basename REGEXP '^" . $pInfo->filename . "(-[0-9]+)?\." . $pInfo->extension . "'", array($directory_id));

                $basename = $pInfo->filename;

                if($similar_file_amount > 0){
//                    $similar_file_amount++;
                    $basename .= "-{$similar_file_amount}";
                }
                $basename .= ".{$pInfo->extension}";
            }

            $pInfo = (object) pathinfo($basename);
            $extension = strtolower($pInfo->extension);
            $filename = $pInfo->filename;
            $basename = $filename . ".{$extension}";
            $type = $this->getType($basename);
            $thumb_file_id = $this->calculateThumbnailId($extension);
            $url = $this->get_value("SELECT directory FROM {$this->tables->directory} WHERE directory_id=?", array($directory_id)) . $basename;
            $resolution = new stdClass();
            $resolution->width = 0;
            $resolution->height = 0;
            $size = 0;

            if (file_exists($file_path)) {
                $size = filesize($file_path);

                if (($type == "image") && ($size > 0)) {
                    global $ADMIN;

                    $ADMIN->IMAGE_PROCESSOR->load($file_path);
                    $resolution = $ADMIN->IMAGE_PROCESSOR->getResolution();
                }
            }

            return (object) array("basename" => $basename,
                        "filename" => $filename,
                        "directory_id" => $directory_id,
                        "url" => $url,
                        "type" => $type,
                        "extension" => $extension,
                        "size" => $size,
                        "creation_time" => $creation_time,
                        "last_update_time" => $creation_time,
                        "width" => $resolution->width,
                        "height" => $resolution->height,
                        "thumb_file_id" => $thumb_file_id,
                        "copied_file_id" => $copied_file_id,
                        "access_type" => $access_type);
        } else
            return false;
    }

    /**
     * Sistem dosyası olarak kayıtlı olan ve istenen isimde olan  dosyanın url'ini döndürür
     * @param $filename
     * @return strings
     */
    public function selectSystemFileByFilename($filename) {
        global $systemurl;

        return $this->get_value("SELECT CONCAT('{$systemurl}',url) AS url FROM {$this->table} WHERE filename=? AND access_type='system'", array($filename));
    }

    /**
     * İstenen directory_id'sine sahip dosyaları listeler
     * @param $directory_id
     * @return array
     */
    public function listFilesByDirectory($directory_id, $access_type = "public") {
        $root = $this->{$access_type . "_root"};

        return $this->get_rows("SELECT *, CONCAT('{$root}',url) AS url FROM {$this->table} WHERE directory_id=? AND access_type=? ORDER BY filename ASC", array($directory_id, $access_type));
    }

    /**
     * İstenen file_id'ye sahip dosyanın bilgilerini döndürür
     * @param $file_id
     * @return mixed
     */
    public function selectFileById($file_id) {
        if ($file = $this->get_row("SELECT * FROM {$this->table} WHERE file_id=?", array($file_id))) {
            $root = $this->{$file->access_type . "_root"};
            $file->url = $root . $file->url;

            return $file;
        } else {
            return false;
        }
    }

    /**
     * istenen file_id'ye sahip dosyanın url'ini döndürür
     * @param $file_id
     * @return strings
     */
    public function selectFileUrlById($file_id) {
        if ($file = $this->selectFileById($file_id)) {
            return $file->url;
        } else {
            return false;
        }
    }

    /**
     * adresi verilen dosyanın veritabanında kaydının olup olmadığını kontrol eder, dönüş olarak file_id değerini gönderir
     * @param $fileurl
     * @return strings
     */
    public function getFileIdByUrl($url, $access_type = "public") {
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
    public function updateFileInfo($file_id, $basename, $filename, $thumb_file_id) {
        if ($file = $this->selectFileById($file_id)) {
            $root = $this->{$file->access_type . "_root"};
            $last_update_time = currentDateTime();
            $new_url = $this->get_value("SELECT directory FROM {$this->table_directory} WHERE directory_id=?", array($file->directory_id)) . $basename;
            $new_url_full_path = $root . $new_url;

            // Dosya ismini güncelle ve database bilgilerini guncelle
            if (rename($file->url, $new_url_full_path)) {
                return $this->execute("UPDATE {$this->table} SET basename=?, filename=?, url=?, thumb_file_id=?, last_update_time=? WHERE file_id=?", array($basename, $filename, $new_url, $thumb_file_id, $last_update_time, $file_id));
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * İstenen file_id'ye sahip dosyanın tüm bilgilerini ve kendisini siler.
     * @param $file_id
     * @return bool
     */
    public function deleteFile($file_id) {
        $file = $this->selectFileById($file_id);

        // dosya mevcutsa sil
        if (file_exists($file->url)) {
            unlink($file->url);
        }

        // dosyanın thumbnaillerini sil
        if (!$this->deleteFileThumbs($file_id)) {
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
    function calculateThumbnailId($extension) {
        if (preg_match("/jpg|jpeg|png|gif$/i", $extension))
            return -1;
        else {
            if ($file_id = $this->get_value("SELECT file_id FROM {$this->table} WHERE filename=? AND access_type='system'", array($extension)))
                return $file_id;
            else
                return $this->get_value("SELECT file_id FROM {$this->table} WHERE filename='generic' AND access_type='system'");
        }
    }

    function selectFileByDirectoryAndName($directory_id, $filename) {
        return $this->get_row("SELECT * FROM {$this->table} WHERE directory_id=? AND filename=?", array($directory_id, $filename));
    }

    // PRIVATE

    private function getType($basename) {
        if (preg_match("/\.jpg|\.jpeg|\.png|\.gif$/i", $basename))
            return "image";
        else if (preg_match("/\.avi|\.mp4|\.flv|\.f4v$/i", $basename))
            return "movie";
        else if (preg_match("/\.mp3$/i", $basename))
            return "sound";
        else
            return "other";
    }

}
