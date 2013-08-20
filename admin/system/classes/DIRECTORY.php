<?php
class PA_DIRECTORY extends PA_FILE
{
	private $table;
    private $public_root;
    private $private_root;
	
	function PA_DIRECTORY(){
		parent::PA_FILE();
		
		$this->table = $this->tables->directory;
		$this->table_file = $this->tables->file;

        global $public_uploadurl;
        global $private_uploadurl;

        $this->public_root = $public_uploadurl;
        $this->private_root = $private_uploadurl;
	}

    /**
     * @param $name dizin adı
     * @param $parent_id parent dizinin id'si
     * @param string $access_type dizine erişim tipini belirtir, "public" veya "private" olabilir.
     * @return bool|string
     */
    function createDirectory($name, $parent_id=-1, $access_type="public"){
        $root = $this->{$access_type . "_root"};

        // database'e kaydedilecek şekilde dizin bilgisini hesapla
        if($parent_directory = $this->selectDirectoryById($parent_id)) // Üst dizin varsa
            $directory = preg_replace("/^" . preg_quote($root, "/") . "/", "", $parent_directory->directory) . $name . "/";
        else
            $directory = $name . "/";

        // dosyayı oluşturmada kullanmak için tam dizin bilgisini hesapla
        $full_path = $root . $directory;

        // dizinleri oluştur ve yetkilerini ata
        if(!file_exists($full_path)){
            if(!mkdir($full_path, 0775)) // dosyayı oluştururken mode değeri desteklenmediği için aşağıda tekrardan chmod işlemi yapıyoruz
                return false;

            if(!chmod($full_path, 0775))
                return false;
        }

		if($dir = $this->selectDirectoryByNameAndParent($parent_id, $name, $access_type)){
            return $dir->directory_id;
        }
        else{
			return $this->insert($this->table, compact("parent_id", "name", "directory", "access_type"));
		}
	}	
	
	function updateDirectory($directory_id, $new_name){
        $directory = $this->selectDirectoryById($directory_id);
        $selected_directory = $this->selectDirectoryByNameAndParent($directory->parent_id, $new_name);

		// Eğer böyle bir dizin yoksa işlemi hatalı olarak sonlandır
		if(!$directory){
			return false;
		}
		else if(($directory->name == $new_name)){// Eğer ismi değişmemişse işlemi sonlandır
			return true;
		}
        else if(($selected_directory->directory_id > 0) && ($selected_directory->directory_id != $directory_id)){ // eğer bulunan dizin başka bir dizine aitse farklı isim kullanması gerekir
            return false;
        }
		else { // Eğer yeni isim, içinde bulunduğu dizinde yok ise güncelleme işemini gerçekleştir
			$error = false;
			
			// Önce database de olmayan ve olmaması gereken ama bizim kolaylık olsun diye database den seçim esnasında CONCAT() ile eklediğimiz
			// root url'i temizle. Çünkü database de root url bu şekilde ekli olarak kayıtlı değil ve aramayı doğru yapabilmek için root url'i
			// temizlememiz gerekiyor.

            $root = $this->{$directory->access_type . "_root"};

			$search_directory = preg_replace(("/^" . preg_quote($root, "/") . "/"), "", $directory->directory);
			
			// Şimdi root url'i temizlenmiş url içinden directory adını yenisiyle değiştir.
			$replace_directory = preg_replace(("/" . preg_quote($directory->name, "/") . "\/?$/"), $new_name . "/", $search_directory);

			// varsa güncellenecek dizinin altında bulunan dosyaların url'lerini güncelle
			if($files = $this->get_rows("SELECT file_id, url FROM {$this->table_file} WHERE directory_id > 0 AND access_type=? AND url LIKE ? '%'",array($directory->access_type, $search_directory))){
				$file_count = sizeof($files);
					
				for($i=0; $i<$file_count; $i++){
					$url = preg_replace(("/^" . preg_quote($search_directory , "/") . "/"), $replace_directory, $files[$i]->url);
					if(!$this->execute("UPDATE {$this->table_file} SET url=? WHERE file_id=?", array($url, $files[$i]->file_id)))
						$error = true;
				}	
			}
			
			// varsa güncellenecek dizinin altındaki tüm dizinlerin directory değerini güncelle
			if($directories = $this->get_rows("SELECT directory_id, directory FROM {$this->table} WHERE parent_id > 0 AND access_type=? AND directory LIKE ? '%'", array($directory->access_type, $search_directory))){
				$directory_count = sizeof($directories);
				
				for($i=0; $i<$directory_count; $i++){
					$new_directory = preg_replace(("/^" . preg_quote($search_directory, "/") . "/"), $replace_directory, $directories[$i]->directory);
					if(!$this->execute("UPDATE {$this->table} SET directory=? WHERE directory_id=?", array($new_directory, $directories[$i]->directory_id)))
						$error = true;
				}
			}
			
			// Son olarak hata yok ise istenen dizini güncelle
			if(!$error){
				if(rename($directory->directory, $root . $replace_directory)){
					return $this->execute("UPDATE {$this->table} SET name=?, directory=? WHERE directory_id=?", array($new_name, $replace_directory, $directory_id));
				}
				else{
					return false;
				}
			}
			else
				return false;
		}
	}
	
	function selectDirectoryById($directory_id){
        if($directory = $this->get_row("SELECT * FROM {$this->table} WHERE directory_id=?", array($directory_id))){
            $root = $this->{$directory->access_type . "_root"};
            $directory->directory = $root . $directory->directory;

            return $directory;
        }
        else{
            return false;
        }

	}
	
	function selectDirectoryByNameAndParent($parent_id, $name, $access_type = "public"){
        $root = $this->{$access_type . "_root"};

        // directory_id hem public hem private dizinlerde -1 olabilir ve her dizinde de aynı isimde dosya bulunabilir. karısıkligi onlemek icin access_type degerini kullaniyoruz
		return $this->get_row("SELECT *, CONCAT('{$root}', directory) AS directory FROM {$this->table} WHERE parent_id=? AND name=? AND access_type=?",array($parent_id, $name, $access_type));
	}

    // parametre olarak verilen dizini parent dizinleri ile birlikte oluşturur.
    function createDirectoryByPath($directory_path, $access_type="public"){
        $root = $this->{$access_type . "_root"};

        $fixed_path = preg_replace("/^.*?" . preg_quote($root, "/") . "/", "", $directory_path);
        $parent_id = -1;

        $path_array = explode("/", $fixed_path);
        $path_amount = sizeof($path_array);

        for($i=0; $i<$path_amount; $i++){
            $directory_name = $path_array[$i];

            if(strlen($directory_name) > 0){
                if(!$parent_id = $this->createDirectory($directory_name, $parent_id, $access_type)){
                    return false;
                }
            }
        }

        return $parent_id;
    }
	
	function listDirectoriesByParentId($parent_id, $access_type = "public"){
        $root = $this->{$access_type . "_root"};

		return $this->get_rows("SELECT *, CONCAT('$root', directory) AS directory FROM {$this->table} WHERE parent_id=? AND access_type=? ORDER BY name ASC", array($parent_id, $access_type));
	}
	
	function listFavouritedDirectories($access_type = "public"){
        $root = $this->{$access_type . "_root"};

		return $this->get_rows("SELECT *, CONCAT('$root', directory) AS directory FROM {$this->table} WHERE is_favourite>0 AND access_type=?", array($access_type));
	}
	
	function setDirectoryFavouriteStatus($directory_id, $status = 1){
		return $this->execute("UPDATE {$this->table} SET is_favourite=? WHERE directory_id=?", array($status, $directory_id));
	}
	
	function generateFileTreeHtmlByParentId($parent_id, $access_type="public"){
		if($dirs = $this->get_rows("SELECT * FROM {$this->table} WHERE parent_id=? AND access_type=?", array($parent_id, $access_type))){
            $dirHtml = '<ul class="fileTree">';
            foreach($dirs as $d){

                if($subTree = $this->generateFileTreeHtmlByParentId($d->directory_id)){
                    $className = "";
                }
                else{
                    $className = "empty";
                }

                $dirHtml .= '<li class="' . $className . '" directory_id="' . $d->directory_id . '">';
                $dirHtml .= '<icon></icon><span class="name">' . $d->name . '</span>';
                $dirHtml .= $subTree;
                $dirHtml .= '</li>';
            }
            $dirHtml .= '</ul>';
        }
        else{
            $dirHtml = "";
        }


		return $dirHtml;
	}
	
	/**
	 * 
	 * dizin silme işlemi esnasında o dizinin içinde bulunan tüm dosya ve klasörlerin ve 
	 * onların alt dosyalarının silinebilmesi için kullanılır.
	 * 
	 * */
	function deleteDirectory($directory_id){
		$d = $this->selectDirectoryById($directory_id);
        $root = $this->{$d->access_type . "_root"};
		
		if($this->deleteDirectoryCompletely($d->directory)) // dizin içindeki tüm dosya ve klasörleri sil
		{
			$d->directory = preg_replace(("/^" . preg_quote($root, "/") . "/"), "", $d->directory);
			
			// Dosyaların thumbnaillerini sil
			if($files = $this->get_rows("SELECT * FROM {$this->table_file} WHERE url LIKE ? '%'",array($d->directory))){
				$count = sizeof($files);
				
				for($i=0; $i<$count; $i++){
					$this->deleteFileThumbs($files[$i]->file_id);
				}
			}
			
			// Dosya ve dizin bilgilerini database den sil
			if(!$this->execute("DELETE FROM {$this->table_file} WHERE access_type=? AND url LIKE ? '%'",array($d->access_type, $d->directory))){
                return false;
            }
            else if(!$this->execute("DELETE FROM {$this->table} WHERE access_type=? AND directory LIKE ? '%'",array($d->access_type, $d->directory))){
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 
	 * girilen dizini içerikleri ile birlikte siler, herhangi bir database işlemi yapmaz, işlemi sadece dosya bazında gerçekleştirir
	 * @param string $directory
	 */
	private function deleteDirectoryCompletely($directory){
		if(!file_exists($directory)){
			return true;
		}
		else if($contents = scandir($directory))
		{
			$count = sizeof($contents);
			for($i=0; $i<$count; $i++){
				$content_name = $contents[$i];
				$content_path = $directory . $content_name;
				
				if(($content_name != ".") && ($content_name != "..")){
					if(is_dir($content_path)){
						$this->deleteDirectoryCompletely($content_path.'/');
					}
					else if(file_exists($content_path)){
						unlink($content_path);
					}
				}
			}
			
			return rmdir($directory);
		}
        else{
            return true;
        }
	}

    /**
     * upload/files/ dizinindeki tüm dizinleri veritabanı ile senkronize hale getirir, kaydı olmayan dizinleri
     * veritabanına ekler, kaydı olupta kendisi olmayan dizinlerin bilgilerini veritabanından siler
     * @return bool
     */
    function synchronizeDirectories($access_type = "public"){
        $root = $this->{$access_type . "_root"};

        // dizinleri database'de ara ve olmayanları database'e ekle.
        $directories = $this->listWholeDirectoriesInPath($root);
        $directory_amount = sizeof($directories);
        $error = false;

        for($i=0; $i<$directory_amount; $i++){
            if(!$this->createDirectoryByPath($directories[$i], $access_type)){
                $error = true;
            }
        }

        // database'deki dizinleri dosya sisteminde ara ve olmayanları database'den sil
        $directories = $this->get_rows("SELECT * FROM {$this->table} WHERE access_type=?", array($access_type));
        foreach($directories as $d){
            if(!is_dir($root . $d->directory)){
                // eğer dizin yoksa veritabanından sil, dosya silme işlemi riskli olacağı için
                // sistemin DIRECTORY class'ındaki fonksiyonu kullanma burda bir query çalıştır.
                $this->execute("DELETE FROM {$this->table} WHERE directory_id=?", array($d->directory_id));
            }
        }

        return !$error;
    }

    /**
     * bir dizin içindeki herhangi bir dizinin içinde bulunan tüm dizinleri yine tüm alt dizinleri ile birlikte döndürür
     * @param $directory_path
     * @return array
     */
    private function listWholeDirectoriesInPath($directory_path){
        $parent_path = preg_replace("/^\/?(.*?)\/?$/","$1", $directory_path) . "/";

        $files = scandir($parent_path);
        $file_amount = sizeof($files);
        $subdirs = array();

        if($file_amount > 2){
            for($i=0; $i<$file_amount; $i++){
                $directory_name = $files[$i];
                $directory = $parent_path . $directory_name . "/";

                // sıradaki data'nın dizin olup olmadığını kontrol et
                if(($directory_name != ".") && ($directory_name != "..") && is_dir($directory)){
                    $subdirs[] = $directory;
                    $subdirs = array_merge($subdirs, $this->listWholeDirectoriesInPath($directory));
                }
            }
        }

        return $subdirs;
    }

    /**
     * İstenen dizinin veritabanında kaydının olup olmadığını kontrol eder, kaydı varsa o dizinin veritabanındaki bilgileri döndürür.
     * @param $directory_path
     * @return mixed
     */
    function checkIfDirectoryExistInDbByPath($directory_path, $access_type="public"){
        $root = $this->{$access_type . "_root"};

        $fixed_path = preg_replace("/^" . preg_quote($root, "/") . "/", "", $directory_path);

        return $this->get_row("SELECT * FROM {$this->table} WHERE directory=?", array($fixed_path));
    }
}